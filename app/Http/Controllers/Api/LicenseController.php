<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ButcherShop;
use App\Models\License;
use App\Models\UnlockCode;
use App\Services\SmsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'License', description: 'License management endpoints')]
class LicenseController extends Controller
{
    #[OA\Get(
        path: '/license/status',
        summary: 'Get license status',
        description: 'Get the current license status for a butcher shop',
        tags: ['License'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'butcher_id', in: 'query', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'License status',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'license', type: 'object', properties: [
                            new OA\Property(property: 'butcher_id', type: 'integer'),
                            new OA\Property(property: 'plan', type: 'string', example: 'free'),
                            new OA\Property(property: 'status', type: 'string', example: 'active'),
                            new OA\Property(property: 'is_locked', type: 'boolean'),
                            new OA\Property(property: 'payment_count', type: 'integer'),
                            new OA\Property(property: 'payment_limit', type: 'integer'),
                            new OA\Property(property: 'remaining_payments', type: 'integer'),
                            new OA\Property(property: 'expires_at', type: 'string', format: 'date-time', nullable: true)
                        ])
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated')
        ]
    )]
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

    #[OA\Post(
        path: '/license/unlock',
        summary: 'Unlock license',
        description: 'Unlock a license using an unlock code to add more payments',
        tags: ['License'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['butcher_id', 'unlock_code'],
                properties: [
                    new OA\Property(property: 'butcher_id', type: 'integer', example: 1),
                    new OA\Property(property: 'unlock_code', type: 'string', example: 'ABC123')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'License unlocked successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'License unlocked'),
                        new OA\Property(property: 'new_payment_limit', type: 'integer'),
                        new OA\Property(property: 'remaining_payments', type: 'integer')
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Invalid unlock code'),
            new OA\Response(response: 401, description: 'Unauthenticated')
        ]
    )]
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

        // Send SMS notification to butcher shop owner
        $butcherShop = ButcherShop::find($request->butcher_id);
        if ($butcherShop && $butcherShop->phone) {
            $smsService = new SmsService();
            $smsService->sendUnlockCodeNotification(
                $butcherShop->phone,
                $request->unlock_code,
                $unlockCode->additional_payments
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'License unlocked',
            'new_payment_limit' => $newLimit,
            'remaining_payments' => $license->remaining_payments,
        ]);
    }
}
