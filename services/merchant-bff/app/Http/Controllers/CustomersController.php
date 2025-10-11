<?php

namespace App\Http\Controllers;

use App\Services\CustomerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomersController extends Controller
{
    public function __construct(
        protected CustomerService $customerService
    ) {}

    /**
     * Display a listing of customers
     */
    public function index(): View
    {
        return view('customers.index');
    }

    /**
     * Get customers data for DataTables
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
                'per_page' => $length,
                'page' => $page,
                'paginate' => true,
            ];

            $response = $this->customerService->getAll($filters);

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
     * Display the specified customer
     */
    public function show(string $id): View
    {
        try {
            $customer = $this->customerService->getById($id);

            return view('customers.show', compact('customer'));
        } catch (\Exception $e) {
            abort(404, 'Customer not found');
        }
    }

    /**
     * Show the form for editing the specified customer
     */
    public function edit(string $id): View
    {
        try {
            $customer = $this->customerService->getById($id);

            return view('customers.edit', compact('customer'));
        } catch (\Exception $e) {
            abort(404, 'Customer not found');
        }
    }

    /**
     * Update the specified customer
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'required|string',
        ]);

        try {
            $this->customerService->update($id, $validated);

            return redirect()->route('customers.index')
                ->with('success', 'Customer updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update customer: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified customer
     */
    public function destroy(string $id): RedirectResponse
    {
        try {
            $this->customerService->delete($id);

            return redirect()->route('customers.index')
                ->with('success', 'Customer deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete customer: ' . $e->getMessage());
        }
    }
}

