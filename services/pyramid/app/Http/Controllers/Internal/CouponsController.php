<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CouponsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Coupon::query();

        if ($request->has('merchant_id')) {
            $query->where('merchant_id', $request->input('merchant_id'));
        }

        if ($request->has('is_enabled')) {
            $query->where('is_enabled', $request->input('is_enabled'));
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $perPage = $request->input('per_page', 15);
        $coupons = $request->boolean('paginate', true) 
            ? $query->paginate($perPage)
            : $query->get();

        return response()->json($coupons);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:coupons,code|max:255',
            'description' => 'nullable|string',
            'merchant_id' => 'nullable|exists:merchants,id',
            'max_per_customer' => 'nullable|integer',
            'total_quantity' => 'nullable|integer',
            'minimum_purchase' => 'nullable|numeric',
            'discount_amount' => 'nullable|numeric',
            'max_discount_amount' => 'nullable|numeric',
            'discount_percentage' => 'nullable|numeric',
            'valid_from' => 'required|date',
            'valid_until' => 'required|date|after:valid_from',
            'is_platform_promotion' => 'boolean',
            'is_enabled' => 'boolean',
        ]);

        $coupon = Coupon::create($validated);
        return response()->json($coupon, 201);
    }

    public function show(string $id): JsonResponse
    {
        $coupon = Coupon::findOrFail($id);
        return response()->json($coupon);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $coupon = Coupon::findOrFail($id);
        
        $validated = $request->validate([
            'code' => 'sometimes|string|max:255|unique:coupons,code,' . $id,
            'description' => 'nullable|string',
            'merchant_id' => 'nullable|exists:merchants,id',
            'max_per_customer' => 'nullable|integer',
            'total_quantity' => 'nullable|integer',
            'minimum_purchase' => 'nullable|numeric',
            'discount_amount' => 'nullable|numeric',
            'max_discount_amount' => 'nullable|numeric',
            'discount_percentage' => 'nullable|numeric',
            'valid_from' => 'sometimes|date',
            'valid_until' => 'sometimes|date',
            'is_platform_promotion' => 'boolean',
            'is_enabled' => 'boolean',
        ]);
        
        $coupon->update($validated);
        return response()->json($coupon);
    }

    public function destroy(string $id): JsonResponse
    {
        Coupon::findOrFail($id)->delete();
        return response()->json(['message' => 'Coupon deleted successfully']);
    }
}

