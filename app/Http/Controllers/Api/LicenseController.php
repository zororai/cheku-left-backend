<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\UnlockCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LicenseController extends Controller
{
    public function status(Request $request): JsonResponse
    {
        $request->validate([
            'butcher_id' => 'required|exists:butcher_shops,id',
        ]);

        $license = License::where('butcher_id', $request->butcher_id)->first();

        if (!$license) {
            // Create default license if not exists
            $license = License::create([
                'butcher_id' => $request->butcher_id,
                'plan' => 'free',
                'status' => 'active',
                'payment_count' => 0,
                'payment_limit' => 100,
            ]);
        }

        return response()->json([
            'success' => true,
            'license' => [
                'butcher_id' => $license->butcher_id,
                'plan' => $license->plan,
                'status' => $license->status,
                'is_locked' => $license->isLocked(),
                'payment_count' => $license->payment_count,
                'payment_limit' => $license->payment_limit,
                'remaining_payments' => $license->remaining_payments,
                'expires_at' => $license->expires_at?->toIso8601String(),
            ],
        ]);
    }

    public function unlock(Request $request): JsonResponse
    {
        $request->validate([
            'butcher_id' => 'required|exists:butcher_shops,id',
            'unlock_code' => 'required|string',
        ]);

        $unlockCode = UnlockCode::where('code', $request->unlock_code)
            ->where('is_used', false)
            ->first();

        if (!$unlockCode) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid unlock code',
            ], 400);
        }

        $license = License::where('butcher_id', $request->butcher_id)->first();

        if (!$license) {
            $license = License::create([
                'butcher_id' => $request->butcher_id,
                'plan' => 'free',
                'status' => 'active',
                'payment_count' => 0,
                'payment_limit' => 100,
            ]);
        }

        // Apply unlock code
        $newLimit = $license->payment_limit + $unlockCode->additional_payments;
        $license->update([
            'payment_limit' => $newLimit,
            'status' => 'active',
        ]);

        $unlockCode->markAsUsed($request->butcher_id);

        return response()->json([
            'success' => true,
            'message' => 'License unlocked',
            'new_payment_limit' => $newLimit,
            'remaining_payments' => $license->remaining_payments,
        ]);
    }
}
