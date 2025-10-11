<?php

namespace App\Http\Controllers\Api\Data;

use App\Models\CustomerAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerAddressesDataController extends BaseDataController
{
    protected function getModelClass(): string
    {
        return CustomerAddress::class;
    }

    protected function applyFilters($query, Request $request): void
    {
        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->input('customer_id'));
        }

        if ($request->has('is_default')) {
            $query->where('is_default', $request->input('is_default'));
        }

        if ($request->has('sort')) {
            $order = $request->input('order', 'asc');
            $query->orderBy($request->input('sort'), $order);
        } else {
            $query->orderBy('created_at', 'desc');
        }
    }

    /**
     * Get addresses by customer
     */
    public function byCustomer(string $customerId): JsonResponse
    {
        $addresses = CustomerAddress::where('customer_id', $customerId)->get();
        return response()->json($addresses);
    }

    /**
     * Set address as default
     */
    public function setDefault(string $id): JsonResponse
    {
        $address = CustomerAddress::find($id);
        
        if (!$address) {
            return response()->json(['error' => 'Address not found'], 404);
        }

        // Unset all other addresses as default for this customer
        CustomerAddress::where('customer_id', $address->customer_id)
            ->update(['is_default' => false]);

        $address->is_default = true;
        $address->save();

        return response()->json($address);
    }
}

