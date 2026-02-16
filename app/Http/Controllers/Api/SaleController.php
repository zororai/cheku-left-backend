<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Sales', description: 'Sales management endpoints')]
class SaleController extends Controller
{
    #[OA\Get(
        path: '/sales',
        summary: 'List sales',
        description: 'Get list of sales with optional filters',
        tags: ['Sales'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'butcher_id', in: 'query', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'date', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'from_date', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'to_date', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'user_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'payment_method', in: 'query', required: false, schema: new OA\Schema(type: 'string'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'List of sales'),
            new OA\Response(response: 401, description: 'Unauthenticated')
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'butcher_id' => 'required|exists:butcher_shops,id',
        ]);

        $butcherId = $request->butcher_id;

        $query = Sale::where('butcher_id', $butcherId)
            ->with(['user:id,name', 'items.product:id,name']);

        if ($request->filled('date')) {
            $query->whereDate('sale_date', $request->date);
        }
        if ($request->filled('from_date')) {
            $query->whereDate('sale_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('sale_date', '<=', $request->to_date);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        $sales = $query->orderByDesc('sale_date')->get();

        // Calculate summary
        $totalAmount = $sales->sum('total_amount');
        $totalTransactions = $sales->count();
        $byPaymentMethod = $sales->groupBy('payment_method')->map(fn($group) => $group->sum('total_amount'));

        return response()->json([
            'success' => true,
            'sales' => $sales->map(fn($sale) => [
                'id' => $sale->id,
                'butcher_id' => $sale->butcher_id,
                'user_id' => $sale->user_id,
                'user_name' => $sale->user?->name,
                'sale_number' => $sale->sale_number,
                'total_amount' => $sale->total_amount,
                'payment_method' => $sale->payment_method,
                'created_at' => $sale->sale_date?->toIso8601String(),
                'items' => $sale->items,
            ]),
            'summary' => [
                'total_amount' => $totalAmount,
                'total_transactions' => $totalTransactions,
                'by_payment_method' => $byPaymentMethod,
            ],
        ]);
    }

    #[OA\Post(
        path: '/sales',
        summary: 'Create a sale',
        description: 'Record a new sale',
        tags: ['Sales'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['device_sale_id', 'total_amount', 'payment_method', 'sale_date', 'items'],
                properties: [
                    new OA\Property(property: 'device_sale_id', type: 'string'),
                    new OA\Property(property: 'sale_number', type: 'string'),
                    new OA\Property(property: 'total_amount', type: 'number'),
                    new OA\Property(property: 'payment_method', type: 'string'),
                    new OA\Property(property: 'sale_date', type: 'string', format: 'date-time'),
                    new OA\Property(property: 'items', type: 'array', items: new OA\Items(type: 'object'))
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Sale created'),
            new OA\Response(response: 401, description: 'Unauthenticated')
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'device_sale_id' => 'required|string',
            'sale_number' => 'nullable|string',
            'total_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'sale_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.weight_grams' => 'required|integer|min:1',
            'items.*.price_per_kg' => 'required|numeric|min:0',
            'items.*.total_price' => 'required|numeric|min:0',
        ]);

        $user = $request->user();
        $butcherId = $user->butcher_id;

        // Check if sale already synced (idempotency)
        $existingSale = Sale::where('butcher_id', $butcherId)
            ->where('device_sale_id', $request->device_sale_id)
            ->first();

        if ($existingSale) {
            return response()->json([
                'message' => 'Sale already synced.',
                'sale' => $existingSale->load('items'),
            ]);
        }

        $sale = DB::transaction(function () use ($request, $user, $butcherId) {
            $sale = Sale::create([
                'butcher_id' => $butcherId,
                'user_id' => $user->id,
                'device_sale_id' => $request->device_sale_id,
                'sale_number' => $request->sale_number,
                'total_amount' => $request->total_amount,
                'payment_method' => $request->payment_method,
                'sale_date' => $request->sale_date,
                'synced_at' => now(),
            ]);

            foreach ($request->items as $item) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'weight_grams' => $item['weight_grams'],
                    'price_per_kg' => $item['price_per_kg'],
                    'total_price' => $item['total_price'],
                ]);
            }

            return $sale;
        });

        return response()->json([
            'message' => 'Sale synced successfully.',
            'sale' => $sale->load('items'),
        ], 201);
    }

    public function sync(Request $request): JsonResponse
    {
        $request->validate([
            'sales' => 'required|array|min:1',
            'sales.*.butcher_id' => 'required|exists:butcher_shops,id',
            'sales.*.user_id' => 'required|exists:users,id',
            'sales.*.sale_number' => 'required|string',
            'sales.*.total_amount' => 'required|numeric|min:0',
            'sales.*.payment_method' => 'required|string|in:cash,ecocash,innbucks,swipe',
            'sales.*.created_at' => 'required|date',
            'sales.*.items' => 'required|array|min:1',
            'sales.*.items.*.product_id' => 'required|exists:products,id',
            'sales.*.items.*.product_name' => 'required|string',
            'sales.*.items.*.weight_grams' => 'required|integer|min:1',
            'sales.*.items.*.price_per_kg' => 'required|numeric|min:0',
            'sales.*.items.*.total_price' => 'required|numeric|min:0',
        ]);

        $syncedSaleNumbers = [];

        foreach ($request->sales as $saleData) {
            $butcherId = $saleData['butcher_id'];

            // Check if already synced (idempotency by sale_number + butcher_id)
            $existingSale = Sale::where('butcher_id', $butcherId)
                ->where('sale_number', $saleData['sale_number'])
                ->first();

            if ($existingSale) {
                // Already synced, skip
                continue;
            }

            DB::transaction(function () use ($saleData, $butcherId, &$syncedSaleNumbers) {
                $sale = Sale::create([
                    'butcher_id' => $butcherId,
                    'user_id' => $saleData['user_id'],
                    'sale_number' => $saleData['sale_number'],
                    'total_amount' => $saleData['total_amount'],
                    'payment_method' => $saleData['payment_method'],
                    'sale_date' => $saleData['created_at'],
                    'synced_at' => now(),
                ]);

                foreach ($saleData['items'] as $item) {
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $item['product_id'],
                        'product_name' => $item['product_name'],
                        'weight_grams' => $item['weight_grams'],
                        'price_per_kg' => $item['price_per_kg'],
                        'total_price' => $item['total_price'],
                    ]);
                }

                // Increment license payment count
                $license = License::where('butcher_id', $butcherId)->first();
                if ($license) {
                    $license->incrementPaymentCount();
                }

                $syncedSaleNumbers[] = $saleData['sale_number'];
            });
        }

        return response()->json([
            'success' => true,
            'message' => 'Sales synced successfully',
            'synced_count' => count($syncedSaleNumbers),
            'synced_sale_numbers' => $syncedSaleNumbers,
        ]);
    }

    public function show(Request $request, Sale $sale): JsonResponse
    {
        $user = $request->user();

        if ($sale->butcher_id !== $user->butcher_id) {
            return response()->json([
                'message' => 'Sale not found.',
            ], 404);
        }

        return response()->json([
            'sale' => $sale->load(['items.product', 'user:id,name']),
        ]);
    }

    public function report(Request $request): JsonResponse
    {
        $user = $request->user();
        $butcherId = $user->butcher_id;

        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        $sales = Sale::where('butcher_id', $butcherId)
            ->whereDate('sale_date', '>=', $request->date_from)
            ->whereDate('sale_date', '<=', $request->date_to);

        $totalSales = $sales->count();
        $totalRevenue = $sales->sum('total_amount');

        $salesByPaymentMethod = Sale::where('butcher_id', $butcherId)
            ->whereDate('sale_date', '>=', $request->date_from)
            ->whereDate('sale_date', '<=', $request->date_to)
            ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('payment_method')
            ->get();

        $dailySales = Sale::where('butcher_id', $butcherId)
            ->whereDate('sale_date', '>=', $request->date_from)
            ->whereDate('sale_date', '<=', $request->date_to)
            ->select(DB::raw('DATE(sale_date) as date'), DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as total'))
            ->groupBy(DB::raw('DATE(sale_date)'))
            ->orderBy('date')
            ->get();

        return response()->json([
            'period' => [
                'from' => $request->date_from,
                'to' => $request->date_to,
            ],
            'summary' => [
                'total_sales' => $totalSales,
                'total_revenue' => $totalRevenue,
            ],
            'by_payment_method' => $salesByPaymentMethod,
            'daily_breakdown' => $dailySales,
        ]);
    }
}
