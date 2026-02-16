<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $butcherId = $request->input('butcher_id', $user->butcher_id);

        $products = Product::where('butcher_id', $butcherId)
            ->when($request->boolean('active_only'), function ($query) {
                $query->where('is_active', true);
            })
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'products' => $products,
        ]);
    }

    public function sync(Request $request): JsonResponse
    {
        $request->validate([
            'butcher_id' => 'required|exists:butcher_shops,id',
            'products' => 'required|array',
            'products.*.name' => 'required|string|max:255',
            'products.*.price_per_kg' => 'required|numeric|min:0',
            'products.*.is_active' => 'required|boolean',
        ]);

        $user = $request->user();
        $butcherId = $request->butcher_id;

        // Verify user has access to this butcher shop
        if ($user->butcher_id !== $butcherId && !$user->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this butcher shop',
            ], 403);
        }

        $createdCount = 0;
        $updatedCount = 0;

        foreach ($request->products as $productData) {
            if (empty($productData['id'])) {
                // Create new product
                Product::create([
                    'butcher_id' => $butcherId,
                    'name' => $productData['name'],
                    'price_per_kg' => $productData['price_per_kg'],
                    'is_active' => $productData['is_active'],
                ]);
                $createdCount++;
            } else {
                // Update existing product
                $product = Product::where('id', $productData['id'])
                    ->where('butcher_id', $butcherId)
                    ->first();

                if ($product) {
                    $product->update([
                        'name' => $productData['name'],
                        'price_per_kg' => $productData['price_per_kg'],
                        'is_active' => $productData['is_active'],
                    ]);
                    $updatedCount++;
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Products synced successfully',
            'synced_count' => $createdCount + $updatedCount,
            'created_count' => $createdCount,
            'updated_count' => $updatedCount,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price_per_kg' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $user = $request->user();

        $product = Product::create([
            'butcher_id' => $user->butcher_id,
            'name' => $request->name,
            'price_per_kg' => $request->price_per_kg,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'product' => $product,
        ], 201);
    }

    public function show(Request $request, Product $product): JsonResponse
    {
        $user = $request->user();

        if ($product->butcher_id !== $user->butcher_id) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'product' => $product,
        ]);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $user = $request->user();

        if ($product->butcher_id !== $user->butcher_id) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'price_per_kg' => 'sometimes|required|numeric|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        $product->update($request->only(['name', 'price_per_kg', 'is_active']));

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'product' => $product,
        ]);
    }

    public function destroy(Request $request, Product $product): JsonResponse
    {
        $user = $request->user();

        if ($product->butcher_id !== $user->butcher_id) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully',
        ]);
    }
}
