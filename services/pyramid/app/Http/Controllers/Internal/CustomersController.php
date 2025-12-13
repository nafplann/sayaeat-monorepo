<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomersController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Customer::query();

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $perPage = $request->input('per_page', 15);
        $customers = $request->boolean('paginate', true) 
            ? $query->paginate($perPage)
            : $query->get();

        return response()->json($customers);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:customers,email',
            'phone' => 'required|string|unique:customers,phone',
        ]);

        $customer = Customer::create($validated);
        return response()->json($customer, 201);
    }

    public function show(string $id): JsonResponse
    {
        $customer = Customer::with(['addresses', 'orders'])->findOrFail($id);
        return response()->json($customer);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $customer = Customer::findOrFail($id);
        $customer->update($request->all());
        return response()->json($customer);
    }

    public function destroy(string $id): JsonResponse
    {
        Customer::findOrFail($id)->delete();
        return response()->json(['message' => 'Customer deleted successfully']);
    }

    public function addresses(string $id): JsonResponse
    {
        $customer = Customer::findOrFail($id);
        $addresses = $customer->addresses;
        return response()->json($addresses);
    }

    public function orders(string $id): JsonResponse
    {
        $customer = Customer::findOrFail($id);
        $orders = $customer->orders()->with(['merchant', 'items'])->get();
        return response()->json($orders);
    }
}

