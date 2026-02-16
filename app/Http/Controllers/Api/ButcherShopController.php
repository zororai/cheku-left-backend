<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ButcherShopController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $butcherShop = $user->butcherShop;

        if (!$butcherShop) {
            return response()->json([
                'message' => 'No butcher shop associated with your account.',
            ], 404);
        }

        $butcherShop->load('subscriptionPlan');

        return response()->json([
            'butcher_shop' => [
                'id' => $butcherShop->id,
                'name' => $butcherShop->name,
                'phone' => $butcherShop->phone,
                'address' => $butcherShop->address,
                'subscription_plan' => $butcherShop->subscriptionPlan?->name,
                'subscription_status' => $butcherShop->subscription_status,
                'subscription_start_date' => $butcherShop->subscription_start_date?->format('Y-m-d'),
                'subscription_end_date' => $butcherShop->subscription_end_date?->format('Y-m-d'),
                'has_api_key' => !empty($butcherShop->api_key),
            ],
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $user = $request->user();
        $butcherShop = $user->butcherShop;

        if (!$butcherShop) {
            return response()->json([
                'message' => 'No butcher shop associated with your account.',
            ], 404);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|nullable|string|max:50',
            'address' => 'sometimes|nullable|string|max:500',
        ]);

        $butcherShop->update($request->only(['name', 'phone', 'address']));

        return response()->json([
            'message' => 'Butcher shop updated successfully.',
            'butcher_shop' => [
                'id' => $butcherShop->id,
                'name' => $butcherShop->name,
                'phone' => $butcherShop->phone,
                'address' => $butcherShop->address,
            ],
        ]);
    }

    public function generateApiKey(Request $request): JsonResponse
    {
        $user = $request->user();
        $butcherShop = $user->butcherShop;

        if (!$butcherShop) {
            return response()->json([
                'message' => 'No butcher shop associated with your account.',
            ], 404);
        }

        $apiKey = $butcherShop->generateApiKey();

        return response()->json([
            'message' => 'API key generated successfully.',
            'api_key' => $apiKey,
        ]);
    }

    public function subscriptionStatus(Request $request): JsonResponse
    {
        $user = $request->user();
        $butcherShop = $user->butcherShop;

        if (!$butcherShop) {
            return response()->json([
                'message' => 'No butcher shop associated with your account.',
            ], 404);
        }

        $butcherShop->load('subscriptionPlan');

        $daysRemaining = null;
        if ($butcherShop->subscription_end_date) {
            $daysRemaining = max(0, now()->diffInDays($butcherShop->subscription_end_date, false));
        }

        return response()->json([
            'subscription' => [
                'plan_name' => $butcherShop->subscriptionPlan?->name,
                'status' => $butcherShop->subscription_status,
                'start_date' => $butcherShop->subscription_start_date?->format('Y-m-d'),
                'end_date' => $butcherShop->subscription_end_date?->format('Y-m-d'),
                'days_remaining' => $daysRemaining,
                'is_active' => $butcherShop->isSubscriptionActive(),
            ],
        ]);
    }
}
