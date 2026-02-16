<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ButcherShop;
use App\Models\Plan;
use App\Models\PlatformPayment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ButcherShopController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ButcherShop::with(['owner:id,name,email', 'subscriptionPlan:id,name']);

        if ($request->has('status')) {
            $query->where('subscription_status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('owner', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $butcherShops = $query->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 20));

        return response()->json($butcherShops);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'shop_name' => 'required|string|max:255',
            'shop_phone' => 'nullable|string|max:50',
            'shop_address' => 'nullable|string|max:500',
            'owner_name' => 'required|string|max:255',
            'owner_email' => 'required|email|unique:users,email',
            'owner_password' => 'required|string|min:6',
            'plan_id' => 'nullable|exists:plans,id',
        ]);

        $result = DB::transaction(function () use ($request) {
            // Create owner user
            $owner = User::create([
                'name' => $request->owner_name,
                'email' => $request->owner_email,
                'password' => Hash::make($request->owner_password),
                'role' => 'owner',
                'is_active' => true,
            ]);

            // Create butcher shop
            $butcherShop = ButcherShop::create([
                'name' => $request->shop_name,
                'phone' => $request->shop_phone,
                'address' => $request->shop_address,
                'owner_id' => $owner->id,
                'api_key' => Str::random(64),
                'subscription_status' => 'expired',
            ]);

            // Link owner to shop
            $owner->butcher_id = $butcherShop->id;
            $owner->save();

            // Activate subscription if plan provided
            if ($request->plan_id) {
                $plan = Plan::find($request->plan_id);
                $butcherShop->activateSubscription($plan);
            }

            return $butcherShop->load(['owner:id,name,email', 'subscriptionPlan:id,name']);
        });

        return response()->json([
            'message' => 'Butcher shop created successfully.',
            'butcher_shop' => $result,
        ], 201);
    }

    public function show(ButcherShop $butcherShop): JsonResponse
    {
        $butcherShop->load(['owner:id,name,email', 'subscriptionPlan']);

        $stats = [
            'total_users' => $butcherShop->users()->count(),
            'total_products' => $butcherShop->products()->count(),
            'total_sales' => $butcherShop->sales()->count(),
            'total_revenue' => $butcherShop->sales()->sum('total_amount'),
        ];

        return response()->json([
            'butcher_shop' => $butcherShop,
            'stats' => $stats,
        ]);
    }

    public function update(Request $request, ButcherShop $butcherShop): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|nullable|string|max:50',
            'address' => 'sometimes|nullable|string|max:500',
        ]);

        $butcherShop->update($request->only(['name', 'phone', 'address']));

        return response()->json([
            'message' => 'Butcher shop updated successfully.',
            'butcher_shop' => $butcherShop,
        ]);
    }

    public function destroy(ButcherShop $butcherShop): JsonResponse
    {
        $butcherShop->delete();

        return response()->json([
            'message' => 'Butcher shop deleted successfully.',
        ]);
    }

    public function suspend(ButcherShop $butcherShop): JsonResponse
    {
        $butcherShop->suspendSubscription();

        return response()->json([
            'message' => 'Butcher shop suspended successfully.',
            'subscription_status' => $butcherShop->subscription_status,
        ]);
    }

    public function activate(Request $request, ButcherShop $butcherShop): JsonResponse
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);

        $plan = Plan::find($request->plan_id);
        $butcherShop->activateSubscription($plan);

        return response()->json([
            'message' => 'Butcher shop activated successfully.',
            'subscription' => [
                'plan' => $plan->name,
                'status' => $butcherShop->subscription_status,
                'end_date' => $butcherShop->subscription_end_date->format('Y-m-d'),
            ],
        ]);
    }

    public function extendSubscription(Request $request, ButcherShop $butcherShop): JsonResponse
    {
        $request->validate([
            'days' => 'required|integer|min:1',
        ]);

        if ($butcherShop->subscription_end_date) {
            $butcherShop->subscription_end_date = $butcherShop->subscription_end_date->addDays($request->days);
        } else {
            $butcherShop->subscription_end_date = now()->addDays($request->days);
        }

        $butcherShop->subscription_status = 'active';
        $butcherShop->save();

        return response()->json([
            'message' => "Subscription extended by {$request->days} days.",
            'subscription_end_date' => $butcherShop->subscription_end_date->format('Y-m-d'),
        ]);
    }

    public function changePlan(Request $request, ButcherShop $butcherShop): JsonResponse
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);

        $plan = Plan::find($request->plan_id);
        $butcherShop->subscription_plan_id = $plan->id;
        $butcherShop->save();

        return response()->json([
            'message' => 'Subscription plan changed successfully.',
            'plan' => $plan->name,
        ]);
    }

    public function resetApiKey(ButcherShop $butcherShop): JsonResponse
    {
        $apiKey = $butcherShop->generateApiKey();

        return response()->json([
            'message' => 'API key reset successfully.',
            'api_key' => $apiKey,
        ]);
    }

    public function sales(Request $request, ButcherShop $butcherShop): JsonResponse
    {
        $query = $butcherShop->sales()->with(['user:id,name']);

        if ($request->has('date_from')) {
            $query->whereDate('sale_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('sale_date', '<=', $request->date_to);
        }

        $sales = $query->orderByDesc('sale_date')
            ->paginate($request->integer('per_page', 20));

        return response()->json($sales);
    }

    public function recordPayment(Request $request, ButcherShop $butcherShop): JsonResponse
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_method' => 'nullable|string',
            'reference_number' => 'nullable|string',
            'activate_subscription' => 'boolean',
        ]);

        $payment = PlatformPayment::create([
            'butcher_id' => $butcherShop->id,
            'plan_id' => $request->plan_id,
            'amount' => $request->amount,
            'payment_date' => $request->payment_date,
            'payment_method' => $request->payment_method,
            'reference_number' => $request->reference_number,
        ]);

        if ($request->boolean('activate_subscription', true)) {
            $plan = Plan::find($request->plan_id);
            $butcherShop->activateSubscription($plan);
        }

        return response()->json([
            'message' => 'Payment recorded successfully.',
            'payment' => $payment,
            'subscription_status' => $butcherShop->fresh()->subscription_status,
        ], 201);
    }
}
