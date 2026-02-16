<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ButcherShop;
use App\Models\PlatformPayment;
use App\Models\Sale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $totalButchers = ButcherShop::count();
        $activeSubscriptions = ButcherShop::where('subscription_status', 'active')->count();
        $expiredSubscriptions = ButcherShop::where('subscription_status', 'expired')->count();
        $suspendedAccounts = ButcherShop::where('subscription_status', 'suspended')->count();

        $totalPlatformRevenue = PlatformPayment::sum('amount');
        $monthlyRevenue = PlatformPayment::whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->sum('amount');

        $totalSalesAllShops = Sale::sum('total_amount');

        return response()->json([
            'butcher_shops' => [
                'total' => $totalButchers,
                'active' => $activeSubscriptions,
                'expired' => $expiredSubscriptions,
                'suspended' => $suspendedAccounts,
            ],
            'revenue' => [
                'total_platform_revenue' => $totalPlatformRevenue,
                'monthly_platform_revenue' => $monthlyRevenue,
                'total_sales_all_shops' => $totalSalesAllShops,
            ],
        ]);
    }

    public function subscriptionOverview(): JsonResponse
    {
        $expiringIn7Days = ButcherShop::where('subscription_status', 'active')
            ->whereDate('subscription_end_date', '<=', now()->addDays(7))
            ->whereDate('subscription_end_date', '>=', now())
            ->with('owner:id,name,email')
            ->get(['id', 'name', 'owner_id', 'subscription_end_date']);

        $recentlyExpired = ButcherShop::where('subscription_status', 'expired')
            ->whereDate('subscription_end_date', '>=', now()->subDays(30))
            ->with('owner:id,name,email')
            ->get(['id', 'name', 'owner_id', 'subscription_end_date']);

        return response()->json([
            'expiring_soon' => $expiringIn7Days,
            'recently_expired' => $recentlyExpired,
        ]);
    }

    public function revenueReport(Request $request): JsonResponse
    {
        $request->validate([
            'year' => 'nullable|integer|min:2020|max:2100',
        ]);

        $year = $request->integer('year', now()->year);

        $monthlyRevenue = PlatformPayment::whereYear('payment_date', $year)
            ->select(
                DB::raw('MONTH(payment_date) as month'),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy(DB::raw('MONTH(payment_date)'))
            ->orderBy('month')
            ->get();

        $revenueByPlan = PlatformPayment::whereYear('payment_date', $year)
            ->join('plans', 'platform_payments.plan_id', '=', 'plans.id')
            ->select('plans.name', DB::raw('SUM(platform_payments.amount) as total'))
            ->groupBy('plans.name')
            ->get();

        return response()->json([
            'year' => $year,
            'monthly_breakdown' => $monthlyRevenue,
            'by_plan' => $revenueByPlan,
        ]);
    }

    public function platformPayments(Request $request): JsonResponse
    {
        $query = PlatformPayment::with(['butcherShop:id,name', 'plan:id,name']);

        if ($request->has('date_from')) {
            $query->whereDate('payment_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('payment_date', '<=', $request->date_to);
        }

        $payments = $query->orderByDesc('payment_date')
            ->paginate($request->integer('per_page', 20));

        return response()->json($payments);
    }
}
