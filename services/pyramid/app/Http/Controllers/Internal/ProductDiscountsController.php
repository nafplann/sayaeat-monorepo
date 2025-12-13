<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Models\ProductDiscount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductDiscountsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ProductDiscount::query()->with('product');

        if ($request->has('product_id')) {
            $query->where('product_id', $request->input('product_id'));
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }

        $perPage = $request->input('per_page', 15);
        $discounts = $request->boolean('paginate', true) 
            ? $query->paginate($perPage)
            : $query->get();

        return response()->json($discounts);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'name' => 'required|string|max:255',
            'discount_value' => 'required|numeric',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        $discount = ProductDiscount::create($validated);
        return response()->json($discount, 201);
    }

    public function show(string $id): JsonResponse
    {
        $discount = ProductDiscount::with('product')->findOrFail($id);
        return response()->json($discount);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $discount = ProductDiscount::findOrFail($id);
        
        $validated = $request->validate([
            'product_id' => 'sometimes|exists:products,id',
            'name' => 'sometimes|string|max:255',
            'discount_value' => 'sometimes|numeric',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date',
        ]);
        
        $discount->update($validated);
        return response()->json($discount);
    }

    public function destroy(string $id): JsonResponse
    {
        ProductDiscount::findOrFail($id)->delete();
        return response()->json(['message' => 'Product discount deleted successfully']);
    }
}

