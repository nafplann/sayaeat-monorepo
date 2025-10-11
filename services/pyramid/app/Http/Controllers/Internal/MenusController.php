<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenusController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Menu::query()->with(['merchant', 'category']);

        if ($request->has('merchant_id')) {
            $query->where('merchant_id', $request->input('merchant_id'));
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }

        $perPage = $request->input('per_page', 15);
        $menus = $request->boolean('paginate', true) 
            ? $query->paginate($perPage)
            : $query->get();

        return response()->json($menus);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'merchant_id' => 'required|exists:merchants,id',
            'category_id' => 'nullable|exists:menu_categories,id',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'status' => 'required',
        ]);

        $menu = Menu::create($validated);
        return response()->json($menu, 201);
    }

    public function show(string $id): JsonResponse
    {
        $menu = Menu::with(['merchant', 'category', 'addons'])->findOrFail($id);
        return response()->json($menu);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $menu = Menu::findOrFail($id);
        $menu->update($request->all());
        return response()->json($menu);
    }

    public function destroy(string $id): JsonResponse
    {
        Menu::findOrFail($id)->delete();
        return response()->json(['message' => 'Menu deleted successfully']);
    }

    public function getByMerchant(string $merchantId): JsonResponse
    {
        $menus = Menu::where('merchant_id', $merchantId)
            ->with('category')
            ->get();
        return response()->json($menus);
    }

    public function toggleStatus(string $id): JsonResponse
    {
        $menu = Menu::findOrFail($id);
        $menu->update([
            'status' => $menu->status === 'active' ? 'inactive' : 'active'
        ]);
        return response()->json($menu);
    }
}

