<?php

namespace App\Http\Controllers;

use App\Services\MenuService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenusController extends Controller
{
    public function __construct(
        protected MenuService $menuService
    ) {}

    /**
     * Display a listing of menus
     */
    public function index(): View
    {
        return view('menus.index');
    }

    /**
     * Get menus data for DataTables
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
                'status' => $request->input('status'),
                'per_page' => $length,
                'page' => $page,
                'paginate' => true,
            ];

            $response = $this->menuService->getAll($filters);

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
     * Get menus by merchant
     */
    public function getByMerchant(string $merchantId): JsonResponse
    {
        try {
            $menus = $this->menuService->getByMerchant($merchantId);

            return response()->json($menus);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for creating a new menu
     */
    public function create(): View
    {
        return view('menus.create');
    }

    /**
     * Store a newly created menu
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'merchant_id' => 'required|exists:merchants,id',
            'category_id' => 'nullable|exists:menu_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'status' => 'required',
        ]);

        try {
            $this->menuService->create($validated);

            return redirect()->route('menus.index')
                ->with('success', 'Menu created successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create menu: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified menu
     */
    public function show(string $id): View
    {
        try {
            $menu = $this->menuService->getById($id);

            return view('menus.show', compact('menu'));
        } catch (\Exception $e) {
            abort(404, 'Menu not found');
        }
    }

    /**
     * Show the form for editing the specified menu
     */
    public function edit(string $id): View
    {
        try {
            $menu = $this->menuService->getById($id);

            return view('menus.edit', compact('menu'));
        } catch (\Exception $e) {
            abort(404, 'Menu not found');
        }
    }

    /**
     * Update the specified menu
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $validated = $request->validate([
            'merchant_id' => 'required|exists:merchants,id',
            'category_id' => 'nullable|exists:menu_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'status' => 'required',
        ]);

        try {
            $this->menuService->update($id, $validated);

            return redirect()->route('menus.index')
                ->with('success', 'Menu updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update menu: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified menu
     */
    public function destroy(string $id): RedirectResponse
    {
        try {
            $this->menuService->delete($id);

            return redirect()->route('menus.index')
                ->with('success', 'Menu deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete menu: ' . $e->getMessage());
        }
    }

    /**
     * Toggle menu status
     */
    public function toggleStatus(string $id): JsonResponse
    {
        try {
            $menu = $this->menuService->toggleStatus($id);

            return response()->json([
                'success' => true,
                'menu' => $menu
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

