<?php

namespace App\Http\Controllers\Api\Data;

use App\Models\StoreOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreOrdersDataController extends BaseDataController
{
    protected function getModelClass(): string
    {
        return StoreOrder::class;
    }

    protected function applyFilters($query, Request $request): void
    {
        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->input('customer_id'));
        }

        if ($request->has('store_id')) {
            $query->where('store_id', $request->input('store_id'));
        }

        if ($request->has('driver_id')) {
            $query->where('driver_id', $request->input('driver_id'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('payment_status')) {
            $query->where('payment_status', $request->input('payment_status'));
        }

        if ($request->has('date_from')) {
            $query->where('created_at', '>=', $request->input('date_from'));
        }

        if ($request->has('date_to')) {
            $query->where('created_at', '<=', $request->input('date_to'));
        }

        if ($request->has('sort')) {
            $order = $request->input('order', 'asc');
            $query->orderBy($request->input('sort'), $order);
        } else {
            $query->orderBy('created_at', 'desc');
        }
    }

    /**
     * Get orders by store
     */
    public function byStore(string $storeId): JsonResponse
    {
        $orders = StoreOrder::where('store_id', $storeId)
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json($orders);
    }
}

