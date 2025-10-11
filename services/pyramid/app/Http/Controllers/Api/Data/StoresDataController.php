<?php

namespace App\Http\Controllers\Api\Data;

use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoresDataController extends BaseDataController
{
    protected function getModelClass(): string
    {
        return Store::class;
    }

    protected function applyFilters($query, Request $request): void
    {
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->input('is_active'));
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
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
     * Toggle store status
     */
    public function toggleStatus(string $id): JsonResponse
    {
        $store = Store::find($id);
        
        if (!$store) {
            return response()->json(['error' => 'Store not found'], 404);
        }

        $store->is_active = !$store->is_active;
        $store->save();

        return response()->json($store);
    }
}

