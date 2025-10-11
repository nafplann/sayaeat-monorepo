<?php

namespace App\Http\Controllers;

use App\Services\PromotionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PromotionsController extends Controller
{
    public function __construct(
        protected PromotionService $promotionService
    ) {}

    public function index(): View
    {
        return view('promotions.index');
    }

    public function datatable(Request $request): JsonResponse
    {
        try {
            $start = $request->input('start', 0);
            $length = $request->input('length', 10);
            $page = ($start / $length) + 1;

            $filters = [
                'search' => $request->input('search.value'),
                'active' => $request->input('active'),
                'per_page' => $length,
                'page' => $page,
                'paginate' => true,
            ];

            $response = $this->promotionService->getAll($filters);

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
        return view('promotions.create');
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            $this->promotionService->create($request->all());
            return redirect()->route('promotions.index')
                ->with('success', 'Promotion created successfully');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Failed to create promotion: ' . $e->getMessage());
        }
    }

    public function show(string $id): View
    {
        try {
            $promotion = $this->promotionService->getById($id);
            return view('promotions.show', compact('promotion'));
        } catch (\Exception $e) {
            abort(404);
        }
    }

    public function edit(string $id): View
    {
        try {
            $promotion = $this->promotionService->getById($id);
            return view('promotions.edit', compact('promotion'));
        } catch (\Exception $e) {
            abort(404);
        }
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        try {
            $this->promotionService->update($id, $request->all());
            return redirect()->route('promotions.index')
                ->with('success', 'Promotion updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Failed to update promotion: ' . $e->getMessage());
        }
    }

    public function destroy(string $id): RedirectResponse
    {
        try {
            $this->promotionService->delete($id);
            return redirect()->route('promotions.index')
                ->with('success', 'Promotion deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete promotion: ' . $e->getMessage());
        }
    }
}

