<?php

namespace App\Http\Controllers\Api\Data;

use App\Models\User;
use Illuminate\Http\Request;

class UsersDataController extends BaseDataController
{
    protected function getModelClass(): string
    {
        return User::class;
    }

    protected function applyFilters($query, Request $request): void
    {
        if ($request->has('email')) {
            $query->where('email', $request->input('email'));
        }

        if ($request->has('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->input('role'));
            });
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
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

