<?php

namespace App\Http\Controllers;

use App\Services\MenuCategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuCategoriesController extends Controller
{
    public function __construct(
        protected MenuCategoryService $menuCategoryService
    ) {}

    /**
     * Display a listing of menu categories
     */
    public function index(): View
    {
        return view('menu-categories.index');
    }

    /**
     * Get menu categories data for DataTables
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
                'merchant_id' => $request->input('merchant_id'),
                'enabled' => $request->input('enabled'),
                'per_page' => $length,
                'page' => $page,
                'paginate' => true,
            ];

            $response = $this->menuCategoryService->getAll($filters);

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
     * Get menu categories by merchant
     */
    public function getByMerchant(string $merchantId): JsonResponse
    {
        try {
            $categories = $this->menuCategoryService->getByMerchant($merchantId);
            return response()->json($categories);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for creating a new menu category
     */
    public function create(): View
    {
        return view('menu-categories.create');
    }

    /**
     * Store a newly created menu category
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            $this->menuCategoryService->create($request->all());
            return redirect()->route('menu-categories.index')
                ->with('success', 'Menu category created successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create menu category: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified menu category
     */
    public function show(string $id): View
    {
        try {
            $category = $this->menuCategoryService->getById($id);
            return view('menu-categories.show', compact('category'));
        } catch (\Exception $e) {
            abort(404);
        }
    }

    /**
     * Show the form for editing the menu category
     */
    public function edit(string $id): View
    {
        try {
            $category = $this->menuCategoryService->getById($id);
            return view('menu-categories.edit', compact('category'));
        } catch (\Exception $e) {
            abort(404);
        }
    }

    /**
     * Update the specified menu category
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        try {
            $this->menuCategoryService->update($id, $request->all());
            return redirect()->route('menu-categories.index')
                ->with('success', 'Menu category updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update menu category: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified menu category
     */
    public function destroy(string $id): RedirectResponse
    {
        try {
            $this->menuCategoryService->delete($id);
            return redirect()->route('menu-categories.index')
                ->with('success', 'Menu category deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete menu category: ' . $e->getMessage());
        }
    }
}

