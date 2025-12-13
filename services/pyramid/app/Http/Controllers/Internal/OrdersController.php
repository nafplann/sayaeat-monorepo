<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    /**
     * Display a listing of orders
     */
    public function index(Request $request): JsonResponse
    {
        $query = Order::query()->with(['customer', 'merchant', 'driver']);

        // Apply filters
        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->input('customer_id'));
        }

        if ($request->has('merchant_id')) {
            $query->where('merchant_id', $request->input('merchant_id'));
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

        // Date range filter
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->input('per_page', 15);
        
        if ($request->boolean('paginate', true)) {
            $orders = $query->paginate($perPage);
        } else {
            $orders = $query->get();
        }

        return response()->json($orders);
    }

    /**
     * Store a newly created order
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'merchant_id' => 'nullable|exists:merchants,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'items' => 'required|array',
            'subtotal' => 'required|numeric',
            'delivery_fee' => 'required|numeric',
            'service_fee' => 'required|numeric',
            'total' => 'required|numeric',
            'payment_method' => 'required',
            'delivery_address' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $order = Order::create($validated);

        // Create order items
        if (isset($validated['items'])) {
            foreach ($validated['items'] as $item) {
                $order->items()->create($item);
            }
        }
        
        $order->load(['items', 'customer', 'merchant']);
        
        return response()->json($order, 201);
    }

    /**
     * Display the specified order
     */
    public function show(string $id): JsonResponse
    {
        $order = Order::with([
            'customer',
            'merchant',
            'driver',
            'items.menu',
            'items.addons'
        ])->findOrFail($id);
        
        return response()->json($order);
    }

    /**
     * Update the specified order
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'status' => 'sometimes|required',
            'payment_status' => 'sometimes|required',
            'driver_id' => 'nullable|exists:drivers,id',
            'notes' => 'nullable|string',
        ]);

        $order->update($validated);
        $order->load(['customer', 'merchant', 'driver', 'items']);
        
        return response()->json($order);
    }

    /**
     * Remove the specified order
     */
    public function destroy(string $id): JsonResponse
    {
        $order = Order::findOrFail($id);
        $order->delete();
        
        return response()->json([
            'message' => 'Order deleted successfully'
        ]);
    }

    /**
     * Process an order
     */
    public function process(Request $request, string $id): JsonResponse
    {
        $order = Order::findOrFail($id);
        
        // Add your order processing logic here
        $order->update([
            'status' => 'processing'
        ]);
        
        return response()->json($order);
    }

    /**
     * Cancel an order
     */
    public function cancel(Request $request, string $id): JsonResponse
    {
        $order = Order::findOrFail($id);
        
        $reason = $request->input('reason', 'Customer requested cancellation');
        
        $order->update([
            'status' => 'cancelled',
            'cancellation_reason' => $reason
        ]);
        
        return response()->json($order);
    }

    /**
     * Reject an order
     */
    public function reject(Request $request, string $id): JsonResponse
    {
        $order = Order::findOrFail($id);
        
        $reason = $request->input('reason', 'Merchant rejected order');
        
        $order->update([
            'status' => 'rejected',
            'rejection_reason' => $reason
        ]);
        
        return response()->json($order);
    }
}

