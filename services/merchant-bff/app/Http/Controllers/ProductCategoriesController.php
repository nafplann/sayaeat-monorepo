<?php

namespace App\Http\Controllers;

use App\Services\ProductCategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductCategoriesController extends Controller
{
    public function __construct(
        protected ProductCategoryService $productCategoryService
    ) {}

    public function index(): View
    {
        return view('product-categories.index');
    }

    public function datatable(Request $request): JsonResponse
    {
        try {
            $start = $request->input('start', 0);
            $length = $request->input('length', 10);
            $page = ($start / $length) + 1;

            $filters = [
                'search' => $request->input('search.value'),
                'merchant_id' => $request->input('merchant_id'),
                'enabled' => $request->input('enabled'),
                'per_page' => $length,
                'page' => $page,
                'paginate' => true,
            ];

            $response = $this->productCategoryService->getAll($filters);

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

    public function getByMerchant(string $merchantId): JsonResponse
    {
        try {
            $categories = $this->productCategoryService->getByMerchant($merchantId);
            return response()->json($categories);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function create(): View
    {
        return view('product-categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            $this->productCategoryService->create($request->all());
            return redirect()->route('product-categories.index')
                ->with('success', 'Product category created successfully');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Failed to create product category: ' . $e->getMessage());
        }
    }

    public function show(string $id): View
    {
        try {
            $category = $this->productCategoryService->getById($id);
            return view('product-categories.show', compact('category'));
        } catch (\Exception $e) {
            abort(404);
        }
    }

    public function edit(string $id): View
    {
        try {
            $category = $this->productCategoryService->getById($id);
            return view('product-categories.edit', compact('category'));
        } catch (\Exception $e) {
            abort(404);
        }
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        try {
            $this->productCategoryService->update($id, $request->all());
            return redirect()->route('product-categories.index')
                ->with('success', 'Product category updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Failed to update product category: ' . $e->getMessage());
        }
    }

    public function destroy(string $id): RedirectResponse
    {
        try {
            $this->productCategoryService->delete($id);
            return redirect()->route('product-categories.index')
                ->with('success', 'Product category deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete product category: ' . $e->getMessage());
        }
    }
}

