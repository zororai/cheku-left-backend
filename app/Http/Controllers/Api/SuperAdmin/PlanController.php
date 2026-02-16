<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index(): JsonResponse
    {
        $plans = Plan::withCount('butcherShops')
            ->orderBy('duration_days')
            ->get();

        return response()->json([
            'plans' => $plans,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
        ]);

        $plan = Plan::create([
            'name' => $request->name,
            'price' => $request->price,
            'duration_days' => $request->duration_days,
        ]);

        return response()->json([
            'message' => 'Plan created successfully.',
            'plan' => $plan,
        ], 201);
    }

    public function show(Plan $plan): JsonResponse
    {
        $plan->loadCount('butcherShops');

        return response()->json([
            'plan' => $plan,
        ]);
    }

    public function update(Request $request, Plan $plan): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|numeric|min:0',
            'duration_days' => 'sometimes|required|integer|min:1',
        ]);

        $plan->update($request->only(['name', 'price', 'duration_days']));

        return response()->json([
            'message' => 'Plan updated successfully.',
            'plan' => $plan,
        ]);
    }

    public function destroy(Plan $plan): JsonResponse
    {
        if ($plan->butcherShops()->exists()) {
            return response()->json([
                'message' => 'Cannot delete plan with active subscriptions.',
            ], 422);
        }

        $plan->delete();

        return response()->json([
            'message' => 'Plan deleted successfully.',
        ]);
    }
}
