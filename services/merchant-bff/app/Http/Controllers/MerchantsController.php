<?php

namespace App\Http\Controllers;

use App\Services\MerchantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MerchantsController extends Controller
{
    public function __construct(
        protected MerchantService $merchantService
    ) {}

    /**
     * Display a listing of merchants
     */
    public function index(): View
    {
        return view('merchants.index');
    }

    /**
     * Get merchants data for DataTables
     */
    public function datatable(Request $request): JsonResponse
    {
        try {
            // Calculate page from DataTables offset
            $start = $request->input('start', 0);
            $length = $request->input('length', 10);
            $page = ($start / $length) + 1;

            // Get filters from DataTables request
            $filters = [
                'search' => $request->input('search.value'),
                'per_page' => $length,
                'page' => $page,
                'paginate' => true, // Enable server-side pagination
            ];

            if ($request->has('status')) {
                $filters['status'] = $request->input('status');
            }

            $response = $this->merchantService->getAll($filters);

            // Laravel pagination response format
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
     * Show the form for creating a new merchant
     */
    public function create(): View
    {
        return view('merchants.create');
    }

    /**
     * Store a newly created merchant
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:merchants,slug',
            'category' => 'required',
            'status' => 'required',
            'phone_number' => 'required|string',
            'address' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'description' => 'nullable|string',
        ]);

        try {
            $this->merchantService->create($validated);

            return redirect()->route('merchants.index')
                ->with('success', 'Merchant created successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create merchant: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified merchant
     */
    public function show(string $id): View
    {
        try {
            $merchant = $this->merchantService->getById($id);

            return view('merchants.show', compact('merchant'));
        } catch (\Exception $e) {
            abort(404, 'Merchant not found');
        }
    }

    /**
     * Show the form for editing the specified merchant
     */
    public function edit(string $id): View
    {
        try {
            $merchant = $this->merchantService->getById($id);

            return view('merchants.edit', compact('merchant'));
        } catch (\Exception $e) {
            abort(404, 'Merchant not found');
        }
    }

    /**
     * Update the specified merchant
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:merchants,slug,' . $id,
            'category' => 'required',
            'status' => 'required',
            'phone_number' => 'required|string',
            'address' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'description' => 'nullable|string',
        ]);

        try {
            $this->merchantService->update($id, $validated);

            return redirect()->route('merchants.index')
                ->with('success', 'Merchant updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update merchant: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified merchant
     */
    public function destroy(string $id): RedirectResponse
    {
        try {
            $this->merchantService->delete($id);

            return redirect()->route('merchants.index')
                ->with('success', 'Merchant deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete merchant: ' . $e->getMessage());
        }
    }

    /**
     * Toggle merchant status
     */
    public function toggleStatus(string $id): JsonResponse
    {
        try {
            $merchant = $this->merchantService->toggleStatus($id);

            return response()->json([
                'success' => true,
                'merchant' => $merchant
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

