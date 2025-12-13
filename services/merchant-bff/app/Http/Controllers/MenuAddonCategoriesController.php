<?php

namespace App\Http\Controllers;

use App\Services\MenuAddonCategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuAddonCategoriesController extends Controller
{
    public function __construct(
        protected MenuAddonCategoryService $menuAddonCategoryService
    ) {}

    public function index(): View
    {
        return view('menu-addon-categories.index');
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
                'is_mandatory' => $request->input('is_mandatory'),
                'per_page' => $length,
                'page' => $page,
                'paginate' => true,
            ];

            $response = $this->menuAddonCategoryService->getAll($filters);

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
        return view('menu-addon-categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            $this->menuAddonCategoryService->create($request->all());
            return redirect()->route('menu-addon-categories.index')
                ->with('success', 'Menu addon category created successfully');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Failed to create menu addon category: ' . $e->getMessage());
        }
    }

    public function show(string $id): View
    {
        try {
            $category = $this->menuAddonCategoryService->getById($id);
            return view('menu-addon-categories.show', compact('category'));
        } catch (\Exception $e) {
            abort(404);
        }
    }

    public function edit(string $id): View
    {
        try {
            $category = $this->menuAddonCategoryService->getById($id);
            return view('menu-addon-categories.edit', compact('category'));
        } catch (\Exception $e) {
            abort(404);
        }
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        try {
            $this->menuAddonCategoryService->update($id, $request->all());
            return redirect()->route('menu-addon-categories.index')
                ->with('success', 'Menu addon category updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Failed to update menu addon category: ' . $e->getMessage());
        }
    }

    public function destroy(string $id): RedirectResponse
    {
        try {
            $this->menuAddonCategoryService->delete($id);
            return redirect()->route('menu-addon-categories.index')
                ->with('success', 'Menu addon category deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete menu addon category: ' . $e->getMessage());
        }
    }

    public function addonDelete(string $id): RedirectResponse
    {
        try {
            $this->menuAddonCategoryService->deleteAddon($id);
            return redirect()->back()
                ->with('success', 'Menu addon deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete menu addon: ' . $e->getMessage());
        }
    }
}

