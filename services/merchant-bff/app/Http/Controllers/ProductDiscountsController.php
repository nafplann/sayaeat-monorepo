<?php

namespace App\Http\Controllers;

use App\Services\ProductDiscountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductDiscountsController extends Controller
{
    public function __construct(
        protected ProductDiscountService $productDiscountService
    ) {}

    public function index(): View
    {
        return view('product-discounts.index');
    }

    public function datatable(Request $request): JsonResponse
    {
        try {
            $start = $request->input('start', 0);
            $length = $request->input('length', 10);
            $page = ($start / $length) + 1;

            $filters = [
                'search' => $request->input('search.value'),
                'product_id' => $request->input('product_id'),
                'per_page' => $length,
                'page' => $page,
                'paginate' => true,
            ];

            $response = $this->productDiscountService->getAll($filters);

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

    public function create(): View
    {
        return view('product-discounts.create');
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            $this->productDiscountService->create($request->all());
            return redirect()->route('product-discounts.index')
                ->with('success', 'Product discount created successfully');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Failed to create product discount: ' . $e->getMessage());
        }
    }

    public function show(string $id): View
    {
        try {
            $discount = $this->productDiscountService->getById($id);
            return view('product-discounts.show', compact('discount'));
        } catch (\Exception $e) {
            abort(404);
        }
    }

    public function edit(string $id): View
    {
        try {
            $discount = $this->productDiscountService->getById($id);
            return view('product-discounts.edit', compact('discount'));
        } catch (\Exception $e) {
            abort(404);
        }
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        try {
            $this->productDiscountService->update($id, $request->all());
            return redirect()->route('product-discounts.index')
                ->with('success', 'Product discount updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Failed to update product discount: ' . $e->getMessage());
        }
    }

    public function destroy(string $id): RedirectResponse
    {
        try {
            $this->productDiscountService->delete($id);
            return redirect()->route('product-discounts.index')
                ->with('success', 'Product discount deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete product discount: ' . $e->getMessage());
        }
    }
}

