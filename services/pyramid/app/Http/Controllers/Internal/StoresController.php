<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoresController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Store::query();

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('category')) {
            $query->where('category', $request->input('category'));
        }

        $perPage = $request->input('per_page', 15);
        $stores = $request->boolean('paginate', true) 
            ? $query->paginate($perPage)
            : $query->get();

        return response()->json($stores);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:stores,slug',
            'category' => 'required',
            'status' => 'required',
            'address' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $store = Store::create($validated);
        return response()->json($store, 201);
    }

    public function show(string $id): JsonResponse
    {
        $store = Store::with(['products'])->findOrFail($id);
        return response()->json($store);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $store = Store::findOrFail($id);
        $store->update($request->all());
        return response()->json($store);
    }

    public function destroy(string $id): JsonResponse
    {
        Store::findOrFail($id)->delete();
        return response()->json(['message' => 'Store deleted successfully']);
    }

    public function products(string $id): JsonResponse
    {
        $store = Store::findOrFail($id);
        $products = $store->products()->with('category')->get();
        return response()->json($products);
    }

    public function toggleStatus(string $id): JsonResponse
    {
        $store = Store::findOrFail($id);
        $store->update([
            'status' => $store->status === 'active' ? 'inactive' : 'active'
        ]);
        return response()->json($store);
    }
}

