<?php

namespace App\Http\Controllers;

use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrdersController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    /**
     * Display a listing of orders
     */
    public function index(): View
    {
        return view('orders.index');
    }

    /**
     * Get orders data for DataTables
     */
    public function datatable(Request $request): JsonResponse
    {
        try {
            // Calculate page from DataTables offset
            $start = $request->input('start', 0);
            $length = $request->input('length', 10);
            $page = ($start / $length) + 1;

            $filters = [
                'search' => $request->input('search.value'),
                'customer_id' => $request->input('customer_id'),
                'merchant_id' => $request->input('merchant_id'),
                'status' => $request->input('status'),
                'payment_status' => $request->input('payment_status'),
                'date_from' => $request->input('date_from'),
                'date_to' => $request->input('date_to'),
                'per_page' => $length,
                'page' => $page,
                'paginate' => true,
            ];

            $response = $this->orderService->getAll($filters);

            return response()->json([
                'draw' => $request->input('draw'),
                'recordsTotal' => $response['total'] ?? 0,
                'recordsFiltered' => $response['total'] ?? 0,
                'data' => $response['data'] ?? []
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get list of orders (non-DataTables)
     */
    public function list(Request $request): JsonResponse
    {
        try {
            $filters = [
                'status' => $request->input('status'),
                'per_page' => 50,
                'paginate' => false,
            ];

            $orders = $this->orderService->getAll($filters);

            return response()->json($orders);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified order
     */
    public function show(string $id): View
    {
        try {
            $order = $this->orderService->getById($id);

            return view('orders.show', compact('order'));
        } catch (\Exception $e) {
            abort(404, 'Order not found');
        }
    }

    /**
     * Process an order
     */
    public function process(Request $request, string $orderId): JsonResponse
    {
        try {
            $order = $this->orderService->process($orderId);

            return response()->json([
                'success' => true,
                'order' => $order,
                'message' => 'Order processed successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject an order
     */
    public function reject(Request $request, string $orderId): JsonResponse
    {
        $request->validate([
            'reason' => 'nullable|string',
        ]);

        try {
            $order = $this->orderService->reject(
                $orderId,
                $request->input('reason')
            );

            return response()->json([
                'success' => true,
                'order' => $order,
                'message' => 'Order rejected successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

