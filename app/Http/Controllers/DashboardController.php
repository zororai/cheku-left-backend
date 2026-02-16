<?php

namespace App\Http\Controllers;

use App\Models\ButcherShop;
use App\Models\PlatformPayment;
use App\Models\Plan;
use App\Models\Sale;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        if ($user->isSuperAdmin()) {
            return $this->superAdminDashboard();
        }

        if ($user->isOwner() || $user->isManager()) {
            return $this->ownerDashboard($user);
        }

        return $this->cashierDashboard($user);
    }

    private function superAdminDashboard(): Response
    {
        $totalButchers = ButcherShop::count();
        $activeSubscriptions = ButcherShop::where('subscription_status', 'active')->count();
        $expiredSubscriptions = ButcherShop::where('subscription_status', 'expired')->count();
        $suspendedAccounts = ButcherShop::where('subscription_status', 'suspended')->count();

        $totalPlatformRevenue = PlatformPayment::sum('amount');
        $monthlyRevenue = PlatformPayment::whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->sum('amount');

        $recentButchers = ButcherShop::with(['owner:id,name,email', 'subscriptionPlan:id,name'])
            ->latest()
            ->take(5)
            ->get();

        $expiringIn7Days = ButcherShop::where('subscription_status', 'active')
            ->whereDate('subscription_end_date', '<=', now()->addDays(7))
            ->whereDate('subscription_end_date', '>=', now())
            ->with('owner:id,name,email')
            ->get();

        $plans = Plan::withCount('butcherShops')->get();

        return Inertia::render('Dashboard', [
            'dashboardType' => 'super_admin',
            'stats' => [
                'totalButchers' => $totalButchers,
                'activeSubscriptions' => $activeSubscriptions,
                'expiredSubscriptions' => $expiredSubscriptions,
                'suspendedAccounts' => $suspendedAccounts,
                'totalPlatformRevenue' => number_format($totalPlatformRevenue, 2),
                'monthlyRevenue' => number_format($monthlyRevenue, 2),
            ],
            'recentButchers' => $recentButchers,
            'expiringIn7Days' => $expiringIn7Days,
            'plans' => $plans,
        ]);
    }

    private function ownerDashboard($user): Response
    {
        $butcherShop = $user->butcherShop;

        if (!$butcherShop) {
            return Inertia::render('Dashboard', [
                'dashboardType' => 'owner',
                'error' => 'No butcher shop associated with your account.',
            ]);
        }

        $todaySales = Sale::where('butcher_id', $butcherShop->id)
            ->whereDate('sale_date', today())
            ->sum('total_amount');

        $totalSales = Sale::where('butcher_id', $butcherShop->id)->count();
        $totalRevenue = Sale::where('butcher_id', $butcherShop->id)->sum('total_amount');

        $recentSales = Sale::where('butcher_id', $butcherShop->id)
            ->with('user:id,name')
            ->latest('sale_date')
            ->take(5)
            ->get();

        return Inertia::render('Dashboard', [
            'dashboardType' => 'owner',
            'butcherShop' => $butcherShop->load('subscriptionPlan'),
            'stats' => [
                'todaySales' => number_format($todaySales, 2),
                'totalSales' => $totalSales,
                'totalRevenue' => number_format($totalRevenue, 2),
            ],
            'recentSales' => $recentSales,
        ]);
    }

    private function cashierDashboard($user): Response
    {
        $butcherShop = $user->butcherShop;

        $todaySales = Sale::where('butcher_id', $butcherShop?->id)
            ->where('user_id', $user->id)
            ->whereDate('sale_date', today())
            ->sum('total_amount');

        $mySalesCount = Sale::where('user_id', $user->id)
            ->whereDate('sale_date', today())
            ->count();

        return Inertia::render('Dashboard', [
            'dashboardType' => 'cashier',
            'stats' => [
                'todaySales' => number_format($todaySales, 2),
                'mySalesCount' => $mySalesCount,
            ],
        ]);
    }
}
