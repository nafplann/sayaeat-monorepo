<?php

namespace App\Http\Controllers;

use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KirimAjaOrdersController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    public function index(): View
    {
        return view('kirim-aja.index');
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
                'service_type' => 'kirim-aja',
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

    public function settings(): View
    {
        return view('kirim-aja.settings');
    }

    public function details(string $orderId): View
    {
        try {
            $order = $this->orderService->getById($orderId);
            return view('kirim-aja.details', compact('order'));
        } catch (\Exception $e) {
            abort(404);
        }
    }

    public function process(string $orderId): View
    {
        try {
            $order = $this->orderService->getById($orderId);
            return view('kirim-aja.process', compact('order'));
        } catch (\Exception $e) {
            abort(404);
        }
    }

    public function update(Request $request, string $orderId): RedirectResponse
    {
        try {
            $this->orderService->update($orderId, $request->all());
            return redirect()->route('kirim-aja.index')
                ->with('success', 'Order updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update order: ' . $e->getMessage());
        }
    }

    public function cancel(string $orderId): RedirectResponse
    {
        try {
            $this->orderService->cancel($orderId);
            return redirect()->back()
                ->with('success', 'Order cancelled successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to cancel order: ' . $e->getMessage());
        }
    }

    public function calculateFees(Request $request): JsonResponse
    {
        try {
            // Calculate fees logic - can be implemented in OrderService
            return response()->json(['fees' => 0]); // Placeholder
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}

