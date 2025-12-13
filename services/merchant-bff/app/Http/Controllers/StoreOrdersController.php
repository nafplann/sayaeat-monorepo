<?php

namespace App\Http\Controllers;

use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StoreOrdersController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    public function index(): View
    {
        return view('store-orders.index');
    }

    public function datatable(Request $request): JsonResponse
    {
        try {
            $start = $request->input('start', 0);
            $length = $request->input('length', 10);
            $page = ($start / $length) + 1;

            $filters = [
                'search' => $request->input('search.value'),
                'status' => $request->input('status'),
                'store_id' => $request->input('store_id'),
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
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function list(Request $request): JsonResponse
    {
        try {
            $filters = [
                'status' => $request->input('status'),
                'paginate' => false,
            ];
            $orders = $this->orderService->getAll($filters);
            return response()->json($orders);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function process(Request $request, string $orderId): RedirectResponse
    {
        try {
            $this->orderService->process($orderId, $request->all());
            return redirect()->back()
                ->with('success', 'Order processed successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to process order: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, string $orderId): RedirectResponse
    {
        try {
            $this->orderService->reject($orderId, $request->all());
            return redirect()->back()
                ->with('success', 'Order rejected successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to reject order: ' . $e->getMessage());
        }
    }
}

