<?php

namespace App\Http\Controllers\Api\Data;

use App\Models\Driver;
use Illuminate\Http\Request;

class DriversDataController extends BaseDataController
{
    protected function getModelClass(): string
    {
        return Driver::class;
    }

    protected function applyFilters($query, Request $request): void
    {
        if ($request->has('phone')) {
            $query->where('phone', $request->input('phone'));
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->input('is_active'));
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
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

