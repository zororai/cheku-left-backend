<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Authentication', description: 'Authentication endpoints')]
class AuthController extends Controller
{
    #[OA\Post(
        path: '/login',
        summary: 'User login',
        description: 'Authenticate user and return access token',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password123')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful login',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'token', type: 'string'),
                        new OA\Property(property: 'user', type: 'object')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Invalid credentials'),
            new OA\Response(response: 403, description: 'Account suspended or subscription expired')
        ]
    )]
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

    #[OA\Post(
        path: '/logout',
        summary: 'User logout',
        description: 'Revoke current access token',
        tags: ['Authentication'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Logged out successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Logged out successfully')
                    ]
                )
            )
        ]
    )]
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }
}
