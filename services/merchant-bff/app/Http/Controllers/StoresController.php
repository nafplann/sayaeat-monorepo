<?php

namespace App\Http\Controllers;

use App\Services\StoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StoresController extends Controller
{
    public function __construct(
        protected StoreService $storeService
    ) {}

    /**
     * Display a listing of stores
     */
    public function index(): View
    {
        return view('stores.index');
    }

    /**
     * Get stores data for DataTables
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
                'status' => $request->input('status'),
                'category' => $request->input('category'),
                'per_page' => $length,
                'page' => $page,
                'paginate' => true,
            ];

            $response = $this->storeService->getAll($filters);

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
     * Show the form for creating a new store
     */
    public function create(): View
    {
        return view('stores.create');
    }

    /**
     * Store a newly created store
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:stores,slug',
            'category' => 'required',
            'status' => 'required',
            'address' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        try {
            $this->storeService->create($validated);

            return redirect()->route('stores.index')
                ->with('success', 'Store created successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create store: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified store
     */
    public function show(string $id): View
    {
        try {
            $store = $this->storeService->getById($id);

            return view('stores.show', compact('store'));
        } catch (\Exception $e) {
            abort(404, 'Store not found');
        }
    }

    /**
     * Show the form for editing the specified store
     */
    public function edit(string $id): View
    {
        try {
            $store = $this->storeService->getById($id);

            return view('stores.edit', compact('store'));
        } catch (\Exception $e) {
            abort(404, 'Store not found');
        }
    }

    /**
     * Update the specified store
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:stores,slug,' . $id,
            'category' => 'required',
            'status' => 'required',
            'address' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        try {
            $this->storeService->update($id, $validated);

            return redirect()->route('stores.index')
                ->with('success', 'Store updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update store: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified store
     */
    public function destroy(string $id): RedirectResponse
    {
        try {
            $this->storeService->delete($id);

            return redirect()->route('stores.index')
                ->with('success', 'Store deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete store: ' . $e->getMessage());
        }
    }

    /**
     * Toggle store status
     */
    public function toggleStatus(string $id): JsonResponse
    {
        try {
            $store = $this->storeService->toggleStatus($id);

            return response()->json([
                'success' => true,
                'store' => $store
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

