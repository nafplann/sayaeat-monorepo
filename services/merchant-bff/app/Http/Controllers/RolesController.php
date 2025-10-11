<?php

namespace App\Http\Controllers;

use App\Services\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RolesController extends Controller
{
    public function __construct(
        protected RoleService $roleService
    ) {}

    public function index(): View
    {
        return view('roles.index');
    }

    public function datatable(Request $request): JsonResponse
    {
        try {
            $start = $request->input('start', 0);
            $length = $request->input('length', 10);
            $page = ($start / $length) + 1;

            $filters = [
                'search' => $request->input('search.value'),
                'per_page' => $length,
                'page' => $page,
                'paginate' => true,
            ];

            $response = $this->roleService->getAll($filters);

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
        return view('roles.create');
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            $this->roleService->create($request->all());
            return redirect()->route('roles.index')
                ->with('success', 'Role created successfully');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Failed to create role: ' . $e->getMessage());
        }
    }

    public function show(string $id): View
    {
        try {
            $role = $this->roleService->getById($id);
            return view('roles.show', compact('role'));
        } catch (\Exception $e) {
            abort(404);
        }
    }

    public function edit(string $id): View
    {
        try {
            $role = $this->roleService->getById($id);
            $permissions = $this->roleService->getPermissions();
            return view('roles.edit', compact('role', 'permissions'));
        } catch (\Exception $e) {
            abort(404);
        }
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        try {
            $this->roleService->update($id, $request->all());
            return redirect()->route('roles.index')
                ->with('success', 'Role updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Failed to update role: ' . $e->getMessage());
        }
    }

    public function destroy(string $id): RedirectResponse
    {
        try {
            $this->roleService->delete($id);
            return redirect()->route('roles.index')
                ->with('success', 'Role deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete role: ' . $e->getMessage());
        }
    }
}

