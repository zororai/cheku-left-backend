<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SalesController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $butcherShop = $user->butcherShop ?? $user->ownedShop;

        if (!$butcherShop) {
            return Inertia::render('Owner/Sales/Index', [
                'sales' => [],
                'stats' => [],
                'error' => 'No butcher shop associated with your account.',
            ]);
        }

        $query = Sale::where('butcher_id', $butcherShop->id)
            ->with(['user:id,name', 'items.product:id,name,unit']);

        // Date filters
        if ($request->filled('date_from')) {
            $query->whereDate('sale_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('sale_date', '<=', $request->date_to);
        }
        if ($request->filled('cashier_id')) {
            $query->where('user_id', $request->cashier_id);
        }

        $sales = $query->latest('sale_date')->paginate(20)->withQueryString();

        // Stats
        $todaySales = Sale::where('butcher_id', $butcherShop->id)
            ->whereDate('sale_date', today())
            ->sum('total_amount');

        $weekSales = Sale::where('butcher_id', $butcherShop->id)
            ->whereBetween('sale_date', [now()->startOfWeek(), now()->endOfWeek()])
            ->sum('total_amount');

        $monthSales = Sale::where('butcher_id', $butcherShop->id)
            ->whereMonth('sale_date', now()->month)
            ->whereYear('sale_date', now()->year)
            ->sum('total_amount');

        $totalTransactions = Sale::where('butcher_id', $butcherShop->id)->count();

        // Cashiers for filter
        $cashiers = $butcherShop->users()->select('id', 'name')->get();

        return Inertia::render('Owner/Sales/Index', [
            'sales' => $sales,
            'stats' => [
                'todaySales' => number_format($todaySales, 2),
                'weekSales' => number_format($weekSales, 2),
                'monthSales' => number_format($monthSales, 2),
                'totalTransactions' => $totalTransactions,
            ],
            'cashiers' => $cashiers,
            'filters' => $request->only(['date_from', 'date_to', 'cashier_id']),
        ]);
    }

    public function show(Request $request, Sale $sale): Response
    {
        $user = $request->user();
        $butcherShop = $user->butcherShop ?? $user->ownedShop;

        if (!$butcherShop || $sale->butcher_id !== $butcherShop->id) {
            abort(403);
        }

        $sale->load(['user:id,name', 'items.product:id,name,unit,price']);

        return Inertia::render('Owner/Sales/Show', [
            'sale' => $sale,
        ]);
    }
}
