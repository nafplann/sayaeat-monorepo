<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Models\MenuAddon;
use App\Models\MenuAddonCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuAddonCategoriesController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = MenuAddonCategory::query()->with(['merchant', 'addons']);

        if ($request->has('merchant_id')) {
            $query->where('merchant_id', $request->input('merchant_id'));
        }

        if ($request->has('is_mandatory')) {
            $query->where('is_mandatory', $request->input('is_mandatory'));
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
            'is_mandatory' => 'required|boolean',
            'max_selection' => 'required|integer',
            'sorting' => 'nullable|integer',
            'addons' => 'nullable|array',
            'addons.*.name' => 'required|string',
            'addons.*.price' => 'required|numeric',
        ]);

        $addonData = $validated['addons'] ?? [];
        unset($validated['addons']);

        $category = MenuAddonCategory::create($validated);

        // Create addons if provided
        if (!empty($addonData)) {
            foreach ($addonData as $addon) {
                MenuAddon::create([
                    'category_id' => $category->id,
                    'name' => $addon['name'],
                    'price' => $addon['price'],
                ]);
            }
        }

        return response()->json($category->load('addons'), 201);
    }

    public function show(string $id): JsonResponse
    {
        $category = MenuAddonCategory::with(['merchant', 'addons'])->findOrFail($id);
        return response()->json($category);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $category = MenuAddonCategory::findOrFail($id);
        
        $validated = $request->validate([
            'merchant_id' => 'sometimes|exists:merchants,id',
            'name' => 'sometimes|string|min:3|max:255',
            'description' => 'nullable|string',
            'is_mandatory' => 'sometimes|boolean',
            'max_selection' => 'sometimes|integer',
            'sorting' => 'nullable|integer',
        ]);
        
        $category->update($validated);
        return response()->json($category->load('addons'));
    }

    public function destroy(string $id): JsonResponse
    {
        $category = MenuAddonCategory::findOrFail($id);
        // Delete associated addons first
        $category->addons()->delete();
        $category->delete();
        
        return response()->json(['message' => 'Menu addon category deleted successfully']);
    }

    public function addonDelete(string $id): JsonResponse
    {
        MenuAddon::findOrFail($id)->delete();
        return response()->json(['message' => 'Menu addon deleted successfully']);
    }
}

