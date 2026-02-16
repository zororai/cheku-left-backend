<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use App\Models\StockSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockSessionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'butcher_id' => 'required|exists:butcher_shops,id',
        ]);

        $query = StockSession::where('butcher_id', $request->butcher_id)
            ->with(['user:id,name', 'stockMovements']);

        if ($request->filled('from_date')) {
            $query->whereDate('opened_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('opened_at', '<=', $request->to_date);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $sessions = $query->latest('opened_at')->get()->map(function ($session) {
            return [
                'id' => $session->id,
                'butcher_id' => $session->butcher_id,
                'user_id' => $session->user_id,
                'user_name' => $session->user?->name,
                'status' => $session->status,
                'opened_at' => $session->opened_at->toIso8601String(),
                'closed_at' => $session->closed_at?->toIso8601String(),
                'notes' => $session->notes,
                'total_opening_grams' => $session->stockMovements->sum('opening_grams'),
                'total_sold_grams' => $session->stockMovements->sum('sold_grams'),
                'total_closing_grams' => $session->stockMovements->sum('closing_grams') ?? 0,
                'total_variance_grams' => $session->stockMovements->sum('variance_grams') ?? 0,
                'stock_movements' => $session->stockMovements,
            ];
        });

        return response()->json([
            'success' => true,
            'sessions' => $sessions,
        ]);
    }

    public function open(Request $request): JsonResponse
    {
        $request->validate([
            'butcher_id' => 'required|exists:butcher_shops,id',
            'user_id' => 'required|exists:users,id',
            'local_session_id' => 'nullable|integer',
            'opened_at' => 'required|date',
            'stock_movements' => 'required|array',
            'stock_movements.*.product_id' => 'required|exists:products,id',
            'stock_movements.*.product_name' => 'required|string',
            'stock_movements.*.opening_grams' => 'required|integer|min:0',
        ]);

        $session = StockSession::create([
            'butcher_id' => $request->butcher_id,
            'user_id' => $request->user_id,
            'local_session_id' => $request->local_session_id,
            'status' => 'open',
            'opened_at' => $request->opened_at,
        ]);

        foreach ($request->stock_movements as $movement) {
            StockMovement::create([
                'session_id' => $session->id,
                'product_id' => $movement['product_id'],
                'product_name' => $movement['product_name'],
                'opening_grams' => $movement['opening_grams'],
                'sold_grams' => 0,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Stock session opened',
            'session_id' => $session->id,
            'local_session_id' => $session->local_session_id,
        ]);
    }

    public function close(Request $request): JsonResponse
    {
        $request->validate([
            'butcher_id' => 'required|exists:butcher_shops,id',
            'user_id' => 'required|exists:users,id',
            'local_session_id' => 'required|integer',
            'notes' => 'nullable|string',
            'opened_at' => 'required|date',
            'closed_at' => 'required|date',
            'stock_movements' => 'required|array',
            'stock_movements.*.product_id' => 'required|exists:products,id',
            'stock_movements.*.product_name' => 'required|string',
            'stock_movements.*.opening_grams' => 'required|integer|min:0',
            'stock_movements.*.sold_grams' => 'required|integer|min:0',
            'stock_movements.*.closing_grams' => 'required|integer|min:0',
            'stock_movements.*.expected_closing_grams' => 'required|integer',
            'stock_movements.*.variance_grams' => 'required|integer',
        ]);

        // Find existing session or create new one
        $session = StockSession::where('butcher_id', $request->butcher_id)
            ->where('local_session_id', $request->local_session_id)
            ->first();

        if (!$session) {
            $session = StockSession::create([
                'butcher_id' => $request->butcher_id,
                'user_id' => $request->user_id,
                'local_session_id' => $request->local_session_id,
                'status' => 'closed',
                'notes' => $request->notes,
                'opened_at' => $request->opened_at,
                'closed_at' => $request->closed_at,
            ]);
        } else {
            $session->update([
                'status' => 'closed',
                'notes' => $request->notes,
                'closed_at' => $request->closed_at,
            ]);
            // Clear existing movements
            $session->stockMovements()->delete();
        }

        $varianceByProduct = [];

        foreach ($request->stock_movements as $movement) {
            StockMovement::create([
                'session_id' => $session->id,
                'product_id' => $movement['product_id'],
                'product_name' => $movement['product_name'],
                'opening_grams' => $movement['opening_grams'],
                'sold_grams' => $movement['sold_grams'],
                'closing_grams' => $movement['closing_grams'],
                'expected_closing_grams' => $movement['expected_closing_grams'],
                'variance_grams' => $movement['variance_grams'],
            ]);

            $varianceByProduct[] = [
                'product_id' => $movement['product_id'],
                'product_name' => $movement['product_name'],
                'variance_grams' => $movement['variance_grams'],
            ];
        }

        $totalVariance = collect($request->stock_movements)->sum('variance_grams');

        return response()->json([
            'success' => true,
            'message' => 'Stock session closed',
            'session_id' => $session->id,
            'total_variance_grams' => $totalVariance,
            'variance_by_product' => $varianceByProduct,
        ]);
    }
}
