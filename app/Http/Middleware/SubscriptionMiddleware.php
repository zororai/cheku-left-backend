<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        // Super Admin bypasses subscription check
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        $butcherShop = $user->butcherShop;

        if (!$butcherShop) {
            return response()->json([
                'message' => 'No butcher shop associated with your account.',
            ], 403);
        }

        // Check if subscription is suspended
        if ($butcherShop->subscription_status === 'suspended') {
            return response()->json([
                'message' => 'Your account has been suspended. Please contact administrator.',
            ], 403);
        }

        // Check if subscription has expired
        if (!$butcherShop->isSubscriptionActive()) {
            return response()->json([
                'message' => 'Your subscription has expired. Please contact administrator.',
            ], 403);
        }

        return $next($request);
    }
}
