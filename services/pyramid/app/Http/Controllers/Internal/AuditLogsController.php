<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OwenIt\Auditing\Models\Audit;

class AuditLogsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Audit::query()->with('user');

        if ($request->has('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->has('auditable_type')) {
            $query->where('auditable_type', $request->input('auditable_type'));
        }

        if ($request->has('event')) {
            $query->where('event', $request->input('event'));
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('auditable_type', 'like', "%{$search}%")
                  ->orWhere('event', 'like', "%{$search}%");
            });
        }

        $perPage = $request->input('per_page', 15);
        $audits = $request->boolean('paginate', true) 
            ? $query->latest()->paginate($perPage)
            : $query->latest()->get();

        return response()->json($audits);
    }

    public function show(string $id): JsonResponse
    {
        $audit = Audit::with('user')->findOrFail($id);
        return response()->json($audit);
    }
}

