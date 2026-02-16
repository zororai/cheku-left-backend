<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Account suspended',
            ], 403);
        }

        // Check subscription status for non-super-admin users
        if (!$user->isSuperAdmin() && $user->butcherShop) {
            $butcherShop = $user->butcherShop;

            if ($butcherShop->subscription_status === 'suspended') {
                return response()->json([
                    'success' => false,
                    'message' => 'Account suspended',
                ], 403);
            }

            if (!$butcherShop->isSubscriptionActive()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Subscription expired',
                ], 403);
            }
        }

        $token = $user->createToken('mobile-app')->plainTextToken;
        $user->load('butcherShop');

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'butcher_id' => $user->butcher_id,
                'butcher' => $user->butcherShop ? [
                    'id' => $user->butcherShop->id,
                    'name' => $user->butcherShop->name,
                    'address' => $user->butcherShop->address,
                    'phone' => $user->butcherShop->phone,
                ] : null,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }
}
