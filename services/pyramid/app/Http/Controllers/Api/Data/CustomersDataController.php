<?php

namespace App\Http\Controllers\Api\Data;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomersDataController extends BaseDataController
{
    protected function getModelClass(): string
    {
        return Customer::class;
    }

    protected function applyFilters($query, Request $request): void
    {
        if ($request->has('phone')) {
            $query->where('phone', $request->input('phone'));
        }

        if ($request->has('email')) {
            $query->where('email', $request->input('email'));
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
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

