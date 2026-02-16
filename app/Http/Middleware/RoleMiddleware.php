<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        }

        if (!$user->is_active) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your account has been deactivated. Please contact administrator.',
                ], 403);
            }
            Auth::logout();
            return redirect()->route('login')->withErrors([
                'email' => 'Your account has been deactivated. Please contact administrator.',
            ]);
        }

        if (empty($roles)) {
            return $next($request);
        }

        if (!$user->hasRole($roles)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'You do not have permission to access this resource.',
                ], 403);
            }
            abort(403, 'You do not have permission to access this resource.');
        }

        return $next($request);
    }
}
