<?php

namespace App\Http\Controllers;

use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogsController extends Controller
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    public function index(): View
    {
        return view('audit-logs.index');
    }

    public function datatable(Request $request): JsonResponse
    {
        try {
            $start = $request->input('start', 0);
            $length = $request->input('length', 10);
            $page = ($start / $length) + 1;

            $filters = [
                'search' => $request->input('search.value'),
                'user_id' => $request->input('user_id'),
                'auditable_type' => $request->input('auditable_type'),
                'event' => $request->input('event'),
                'per_page' => $length,
                'page' => $page,
                'paginate' => true,
            ];

            $response = $this->auditLogService->getAll($filters);

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

    public function show(string $id): View
    {
        try {
            $auditLog = $this->auditLogService->getById($id);
            return view('audit-logs.show', compact('auditLog'));
        } catch (\Exception $e) {
            abort(404);
        }
    }
}

