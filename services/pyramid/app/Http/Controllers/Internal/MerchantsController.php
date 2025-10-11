<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Models\Merchant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MerchantsController extends Controller
{
    /**
     * Display a listing of merchants
     */
    public function index(Request $request): JsonResponse
    {
        $query = Merchant::query();

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        // Pagination
        $perPage = $request->input('per_page', 15);
        
        if ($request->boolean('paginate', true)) {
            $merchants = $query->paginate($perPage);
        } else {
            $merchants = $query->get();
        }

        return response()->json($merchants);
    }

    /**
     * Store a newly created merchant
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:merchants,slug',
            'category' => 'required',
            'status' => 'required',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $merchant = Merchant::create($validated);
        
        return response()->json($merchant, 201);
    }

    /**
     * Display the specified merchant
     */
    public function show(string $id): JsonResponse
    {
        $merchant = Merchant::with(['menus', 'menuCategories'])->findOrFail($id);
        
        return response()->json($merchant);
    }

    /**
     * Update the specified merchant
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $merchant = Merchant::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|unique:merchants,slug,' . $id,
            'category' => 'sometimes|required',
            'status' => 'sometimes|required',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $merchant->update($validated);
        
        return response()->json($merchant);
    }

    /**
     * Remove the specified merchant
     */
    public function destroy(string $id): JsonResponse
    {
        $merchant = Merchant::findOrFail($id);
        $merchant->delete();
        
        return response()->json([
            'message' => 'Merchant deleted successfully'
        ]);
    }

    /**
     * Get menus for a specific merchant
     */
    public function menus(string $id): JsonResponse
    {
        $merchant = Merchant::findOrFail($id);
        $menus = $merchant->menus()->with('category')->get();
        
        return response()->json($menus);
    }

    /**
     * Get menu categories for a specific merchant
     */
    public function menuCategories(string $id): JsonResponse
    {
        $merchant = Merchant::findOrFail($id);
        $categories = $merchant->menuCategories;
        
        return response()->json($categories);
    }

    /**
     * Toggle merchant status
     */
    public function toggleStatus(string $id): JsonResponse
    {
        $merchant = Merchant::findOrFail($id);
        
        // Toggle logic - adjust based on your actual status enum
        // This is a placeholder
        $merchant->update([
            'status' => $merchant->status === 'active' ? 'inactive' : 'active'
        ]);
        
        return response()->json($merchant);
    }
}

