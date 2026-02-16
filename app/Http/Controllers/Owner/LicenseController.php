<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\Sale;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LicenseController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $butcherShop = $user->butcherShop ?? $user->ownedShop;

        if (!$butcherShop) {
            return Inertia::render('Owner/License/Index', [
                'license' => null,
                'error' => 'No butcher shop associated with your account.',
            ]);
        }

        $license = License::where('butcher_id', $butcherShop->id)->first();

        if (!$license) {
            $license = License::create([
                'butcher_id' => $butcherShop->id,
                'plan' => 'free',
                'status' => 'active',
                'payment_count' => 0,
                'payment_limit' => 100,
            ]);
        }

        // Recent sales count
        $todaySales = Sale::where('butcher_id', $butcherShop->id)
            ->whereDate('sale_date', today())
            ->count();

        $monthSales = Sale::where('butcher_id', $butcherShop->id)
            ->whereMonth('sale_date', now()->month)
            ->whereYear('sale_date', now()->year)
            ->count();

        return Inertia::render('Owner/License/Index', [
            'license' => [
                'plan' => $license->plan,
                'status' => $license->status,
                'is_locked' => $license->isLocked(),
                'payment_count' => $license->payment_count,
                'payment_limit' => $license->payment_limit,
                'remaining_payments' => $license->remaining_payments,
                'expires_at' => $license->expires_at?->format('Y-m-d'),
            ],
            'shop' => [
                'name' => $butcherShop->name,
                'subscription_status' => $butcherShop->subscription_status,
                'subscription_end_date' => $butcherShop->subscription_end_date?->format('Y-m-d'),
            ],
            'stats' => [
                'todaySales' => $todaySales,
                'monthSales' => $monthSales,
            ],
        ]);
    }
}
