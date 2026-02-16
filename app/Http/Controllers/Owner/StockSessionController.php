<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\StockSession;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StockSessionController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $butcherShop = $user->butcherShop ?? $user->ownedShop;

        if (!$butcherShop) {
            return Inertia::render('Owner/StockSessions/Index', [
                'sessions' => [],
                'stats' => [],
                'error' => 'No butcher shop associated with your account.',
            ]);
        }

        $query = StockSession::where('butcher_id', $butcherShop->id)
            ->with(['user:id,name', 'stockMovements']);

        // Date filters
        if ($request->filled('date_from')) {
            $query->whereDate('opened_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('opened_at', '<=', $request->date_to);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $sessions = $query->latest('opened_at')->paginate(20)->withQueryString();

        // Stats
        $totalSessions = StockSession::where('butcher_id', $butcherShop->id)->count();
        $openSessions = StockSession::where('butcher_id', $butcherShop->id)->where('status', 'open')->count();
        
        $totalVariance = StockSession::where('butcher_id', $butcherShop->id)
            ->where('status', 'closed')
            ->with('stockMovements')
            ->get()
            ->sum(fn($s) => $s->stockMovements->sum('variance_grams'));

        return Inertia::render('Owner/StockSessions/Index', [
            'sessions' => $sessions,
            'stats' => [
                'totalSessions' => $totalSessions,
                'openSessions' => $openSessions,
                'totalVariance' => number_format($totalVariance / 1000, 2) . ' kg',
            ],
            'filters' => $request->only(['date_from', 'date_to', 'status']),
        ]);
    }

    public function show(Request $request, StockSession $stockSession): Response
    {
        $user = $request->user();
        $butcherShop = $user->butcherShop ?? $user->ownedShop;

        if (!$butcherShop || $stockSession->butcher_id !== $butcherShop->id) {
            abort(403);
        }

        $stockSession->load(['user:id,name', 'stockMovements.product:id,name']);

        return Inertia::render('Owner/StockSessions/Show', [
            'session' => $stockSession,
        ]);
    }
}
