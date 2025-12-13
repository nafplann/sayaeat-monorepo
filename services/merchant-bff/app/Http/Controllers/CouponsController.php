<?php

namespace App\Http\Controllers;

use App\Services\CouponService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CouponsController extends Controller
{
    public function __construct(
        protected CouponService $couponService
    ) {}

    public function index(): View
    {
        return view('coupons.index');
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
                'is_enabled' => $request->input('is_enabled'),
                'per_page' => $length,
                'page' => $page,
                'paginate' => true,
            ];

            $response = $this->couponService->getAll($filters);

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
        return view('coupons.create');
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            $this->couponService->create($request->all());
            return redirect()->route('coupons.index')
                ->with('success', 'Coupon created successfully');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Failed to create coupon: ' . $e->getMessage());
        }
    }

    public function show(string $id): View
    {
        try {
            $coupon = $this->couponService->getById($id);
            return view('coupons.show', compact('coupon'));
        } catch (\Exception $e) {
            abort(404);
        }
    }

    public function edit(string $id): View
    {
        try {
            $coupon = $this->couponService->getById($id);
            return view('coupons.edit', compact('coupon'));
        } catch (\Exception $e) {
            abort(404);
        }
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        try {
            $this->couponService->update($id, $request->all());
            return redirect()->route('coupons.index')
                ->with('success', 'Coupon updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Failed to update coupon: ' . $e->getMessage());
        }
    }

    public function destroy(string $id): RedirectResponse
    {
        try {
            $this->couponService->delete($id);
            return redirect()->route('coupons.index')
                ->with('success', 'Coupon deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete coupon: ' . $e->getMessage());
        }
    }
}

