<?php

namespace App\Http\Controllers\Api\Data;

use App\Models\MenuCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuCategoriesDataController extends BaseDataController
{
    protected function getModelClass(): string
    {
        return MenuCategory::class;
    }

    protected function applyFilters($query, Request $request): void
    {
        if ($request->has('merchant_id')) {
            $query->where('merchant_id', $request->input('merchant_id'));
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->has('sort')) {
            $order = $request->input('order', 'asc');
            $query->orderBy($request->input('sort'), $order);
        } else {
            $query->orderBy('created_at', 'desc');
        }
    }

    /**
     * Get menu categories by merchant
     */
    public function byMerchant(string $merchantId): JsonResponse
    {
        $categories = MenuCategory::where('merchant_id', $merchantId)->get();
        return response()->json($categories);
    }
}

