<?php

namespace App\Http\Controllers\Api\Data;

use App\Models\Promotion;
use Illuminate\Http\Request;

class PromotionsDataController extends BaseDataController
{
    protected function getModelClass(): string
    {
        return Promotion::class;
    }

    protected function applyFilters($query, Request $request): void
    {
        if ($request->has('is_active')) {
            $query->where('is_active', $request->input('is_active'));
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
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
}

