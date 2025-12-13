<?php

namespace App\Http\Controllers;

use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductsController extends Controller
{
    public function __construct(
        protected ProductService $productService
    ) {}

    /**
     * Display a listing of products
     */
    public function index(): View
    {
        return view('products.index');
    }

    /**
     * Get products data for DataTables
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
                'store_id' => $request->input('store_id'),
                'category_id' => $request->input('category_id'),
                'per_page' => $length,
                'page' => $page,
                'paginate' => true,
            ];

            $response = $this->productService->getAll($filters);

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
     * Get products by store
     */
    public function getByStore(string $storeId): JsonResponse
    {
        try {
            $products = $this->productService->getByStore($storeId);

            return response()->json($products);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for creating a new product
     */
    public function create(): View
    {
        return view('products.create');
    }

    /**
     * Store a newly created product
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'store_id' => 'required|exists:stores,id',
            'category_id' => 'nullable|exists:product_categories,id',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'unit' => 'required',
            'stock' => 'nullable|integer|min:0',
        ]);

        try {
            $this->productService->create($validated);

            return redirect()->route('products.index')
                ->with('success', 'Product created successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create product: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified product
     */
    public function show(string $id): View
    {
        try {
            $product = $this->productService->getById($id);

            return view('products.show', compact('product'));
        } catch (\Exception $e) {
            abort(404, 'Product not found');
        }
    }

    /**
     * Show the form for editing the specified product
     */
    public function edit(string $id): View
    {
        try {
            $product = $this->productService->getById($id);

            return view('products.edit', compact('product'));
        } catch (\Exception $e) {
            abort(404, 'Product not found');
        }
    }

    /**
     * Update the specified product
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $validated = $request->validate([
            'store_id' => 'required|exists:stores,id',
            'category_id' => 'nullable|exists:product_categories,id',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'unit' => 'required',
            'stock' => 'nullable|integer|min:0',
        ]);

        try {
            $this->productService->update($id, $validated);

            return redirect()->route('products.index')
                ->with('success', 'Product updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update product: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified product
     */
    public function destroy(string $id): RedirectResponse
    {
        try {
            $this->productService->delete($id);

            return redirect()->route('products.index')
                ->with('success', 'Product deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete product: ' . $e->getMessage());
        }
    }

    /**
     * Toggle product status
     */
    public function toggleStatus(string $id): JsonResponse
    {
        try {
            $product = $this->productService->toggleStatus($id);

            return response()->json([
                'success' => true,
                'product' => $product
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

