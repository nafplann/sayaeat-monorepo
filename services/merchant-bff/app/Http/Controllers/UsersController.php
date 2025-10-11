<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UsersController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    public function index(): View
    {
        return view('users.index');
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

            $response = $this->userService->getAll($filters);

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
        return view('users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            $this->userService->create($request->all());
            return redirect()->route('users.index')
                ->with('success', 'User created successfully');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Failed to create user: ' . $e->getMessage());
        }
    }

    public function show(string $id): View
    {
        try {
            $user = $this->userService->getById($id);
            return view('users.show', compact('user'));
        } catch (\Exception $e) {
            abort(404);
        }
    }

    public function edit(string $id): View
    {
        try {
            $user = $this->userService->getById($id);
            return view('users.edit', compact('user'));
        } catch (\Exception $e) {
            abort(404);
        }
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        try {
            $this->userService->update($id, $request->all());
            return redirect()->route('users.index')
                ->with('success', 'User updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Failed to update user: ' . $e->getMessage());
        }
    }

    public function destroy(string $id): RedirectResponse
    {
        try {
            $this->userService->delete($id);
            return redirect()->route('users.index')
                ->with('success', 'User deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete user: ' . $e->getMessage());
        }
    }
}

