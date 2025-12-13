<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductCategoriesController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ProductCategory::query()->with('merchant');

        if ($request->has('merchant_id')) {
            $query->where('merchant_id', $request->input('merchant_id'));
        }

        if ($request->has('enabled')) {
            $query->where('enabled', $request->input('enabled'));
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }

        $perPage = $request->input('per_page', 15);
        $categories = $request->boolean('paginate', true) 
            ? $query->paginate($perPage)
            : $query->get();

        return response()->json($categories);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'merchant_id' => 'required|exists:merchants,id',
            'name' => 'required|string|min:3|max:255',
            'description' => 'nullable|string',
            'enabled' => 'required|boolean',
            'sorting' => 'required|integer',
        ]);

        $category = ProductCategory::create($validated);
        return response()->json($category, 201);
    }

    public function show(string $id): JsonResponse
    {
        $category = ProductCategory::with('merchant')->findOrFail($id);
        return response()->json($category);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $category = ProductCategory::findOrFail($id);
        
        $validated = $request->validate([
            'merchant_id' => 'sometimes|exists:merchants,id',
            'name' => 'sometimes|string|min:3|max:255',
            'description' => 'nullable|string',
            'enabled' => 'sometimes|boolean',
            'sorting' => 'sometimes|integer',
        ]);
        
        $category->update($validated);
        return response()->json($category);
    }

    public function destroy(string $id): JsonResponse
    {
        ProductCategory::findOrFail($id)->delete();
        return response()->json(['message' => 'Product category deleted successfully']);
    }

    public function getByMerchant(string $merchantId): JsonResponse
    {
        $categories = ProductCategory::where('merchant_id', $merchantId)
            ->orderBy('sorting')
            ->get();
        return response()->json($categories);
    }
}

