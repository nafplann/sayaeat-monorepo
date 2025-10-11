<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::query()->with(['category', 'store']);

        if ($request->has('store_id')) {
            $query->where('store_id', $request->input('store_id'));
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }

        $perPage = $request->input('per_page', 15);
        $products = $request->boolean('paginate', true) 
            ? $query->paginate($perPage)
            : $query->get();

        return response()->json($products);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'store_id' => 'required|exists:stores,id',
            'category_id' => 'nullable|exists:product_categories,id',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'unit' => 'required',
            'stock' => 'nullable|integer',
        ]);

        $product = Product::create($validated);
        return response()->json($product, 201);
    }

    public function show(string $id): JsonResponse
    {
        $product = Product::with(['category', 'store'])->findOrFail($id);
        return response()->json($product);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $product->update($request->all());
        return response()->json($product);
    }

    public function destroy(string $id): JsonResponse
    {
        Product::findOrFail($id)->delete();
        return response()->json(['message' => 'Product deleted successfully']);
    }

    public function getByStore(string $storeId): JsonResponse
    {
        $products = Product::where('store_id', $storeId)
            ->with('category')
            ->get();
        return response()->json($products);
    }

    public function toggleStatus(string $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $product->update([
            'status' => $product->status === 'active' ? 'inactive' : 'active'
        ]);
        return response()->json($product);
    }
}

