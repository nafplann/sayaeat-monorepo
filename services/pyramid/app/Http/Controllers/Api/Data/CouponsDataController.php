<?php

namespace App\Http\Controllers\Api\Data;

use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponsDataController extends BaseDataController
{
    protected function getModelClass(): string
    {
        return Coupon::class;
    }

    protected function applyFilters($query, Request $request): void
    {
        if ($request->has('code')) {
            $query->where('code', $request->input('code'));
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->input('is_active'));
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
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

