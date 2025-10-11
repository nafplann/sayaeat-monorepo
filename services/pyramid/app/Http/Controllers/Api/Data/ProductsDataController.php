<?php

namespace App\Http\Controllers\Api\Data;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductsDataController extends BaseDataController
{
    protected function getModelClass(): string
    {
        return Product::class;
    }

    protected function applyFilters($query, Request $request): void
    {
        if ($request->has('store_id')) {
            $query->where('store_id', $request->input('store_id'));
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->has('is_available')) {
            $query->where('is_available', $request->input('is_available'));
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->has('sort')) {
            $order = $request->input('order', 'asc');
            $query->orderBy($request->input('sort'), $order);
        } else {
            $query->orderBy('created_at', 'desc');
        }
    }

    /**
     * Get products by store
     */
    public function byStore(string $storeId): JsonResponse
    {
        $products = Product::where('store_id', $storeId)->get();
        return response()->json($products);
    }

    /**
     * Toggle product status
     */
    public function toggleStatus(string $id): JsonResponse
    {
        $product = Product::find($id);
        
        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $product->is_available = !$product->is_available;
        $product->save();

        return response()->json($product);
    }
}

