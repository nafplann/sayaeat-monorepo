<?php

namespace App\Http\Controllers\Api\Data;

use App\Models\ProductCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductCategoriesDataController extends BaseDataController
{
    protected function getModelClass(): string
    {
        return ProductCategory::class;
    }

    protected function applyFilters($query, Request $request): void
    {
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
}

