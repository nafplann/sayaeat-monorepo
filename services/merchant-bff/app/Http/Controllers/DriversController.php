<?php

namespace App\Http\Controllers;

use App\Services\DriverService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DriversController extends Controller
{
    public function __construct(
        protected DriverService $driverService
    ) {}

    public function index(): View
    {
        return view('drivers.index');
    }

    public function datatable(Request $request): JsonResponse
    {
        try {
            $start = $request->input('start', 0);
            $length = $request->input('length', 10);
            $page = ($start / $length) + 1;

            $filters = [
                'search' => $request->input('search.value'),
                'employment_status' => $request->input('employment_status'),
                'per_page' => $length,
                'page' => $page,
                'paginate' => true,
            ];

            $response = $this->driverService->getAll($filters);

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
        return view('drivers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            $this->driverService->create($request->all());
            return redirect()->route('drivers.index')
                ->with('success', 'Driver created successfully');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Failed to create driver: ' . $e->getMessage());
        }
    }

    public function show(string $id): View
    {
        try {
            $driver = $this->driverService->getById($id);
            return view('drivers.show', compact('driver'));
        } catch (\Exception $e) {
            abort(404);
        }
    }

    public function edit(string $id): View
    {
        try {
            $driver = $this->driverService->getById($id);
            return view('drivers.edit', compact('driver'));
        } catch (\Exception $e) {
            abort(404);
        }
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        try {
            $this->driverService->update($id, $request->all());
            return redirect()->route('drivers.index')
                ->with('success', 'Driver updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Failed to update driver: ' . $e->getMessage());
        }
    }

    public function destroy(string $id): RedirectResponse
    {
        try {
            $this->driverService->delete($id);
            return redirect()->route('drivers.index')
                ->with('success', 'Driver deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete driver: ' . $e->getMessage());
        }
    }
}

