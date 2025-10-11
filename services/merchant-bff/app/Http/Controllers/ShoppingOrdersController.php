<?php

namespace App\Http\Controllers;

use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShoppingOrdersController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    public function index(): View
    {
        return view('shopping-orders.index');
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
                'service_type' => 'shopping',
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

    public function fees(): JsonResponse
    {
        try {
            return response()->json(['fees' => 0]); // Placeholder
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function whatsappTemplate(): JsonResponse
    {
        try {
            return response()->json(['template' => '']); // Placeholder
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function create(): View
    {
        return view('shopping-orders.create');
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            $this->orderService->create($request->all());
            return redirect()->route('shopping-orders.index')
                ->with('success', 'Shopping order created successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create shopping order: ' . $e->getMessage());
        }
    }

    public function show(string $id): View
    {
        try {
            $order = $this->orderService->getById($id);
            return view('shopping-orders.show', compact('order'));
        } catch (\Exception $e) {
            abort(404);
        }
    }

    public function edit(string $id): View
    {
        try {
            $order = $this->orderService->getById($id);
            return view('shopping-orders.edit', compact('order'));
        } catch (\Exception $e) {
            abort(404);
        }
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        try {
            $this->orderService->update($id, $request->all());
            return redirect()->route('shopping-orders.index')
                ->with('success', 'Shopping order updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update shopping order: ' . $e->getMessage());
        }
    }

    public function destroy(string $id): RedirectResponse
    {
        try {
            $this->orderService->delete($id);
            return redirect()->route('shopping-orders.index')
                ->with('success', 'Shopping order deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete shopping order: ' . $e->getMessage());
        }
    }
}

