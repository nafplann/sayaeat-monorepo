<?php

namespace App\Http\Controllers\Api\Data;

use App\Models\Menu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenusDataController extends BaseDataController
{
    protected function getModelClass(): string
    {
        return Menu::class;
    }

    protected function applyFilters($query, Request $request): void
    {
        if ($request->has('merchant_id')) {
            $query->where('merchant_id', $request->input('merchant_id'));
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
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
     * Get menus by merchant
     */
    public function byMerchant(string $merchantId): JsonResponse
    {
        $menus = Menu::where('merchant_id', $merchantId)->get();
        return response()->json($menus);
    }

    /**
     * Toggle menu status
     */
    public function toggleStatus(string $id): JsonResponse
    {
        $menu = Menu::find($id);
        
        if (!$menu) {
            return response()->json(['error' => 'Menu not found'], 404);
        }

        $menu->is_available = !$menu->is_available;
        $menu->save();

        return response()->json($menu);
    }
}

