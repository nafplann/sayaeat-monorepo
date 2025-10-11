<?php

namespace App\Http\Controllers;

use App\Enums\PermissionsEnum;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use OwenIt\Auditing\Models\Audit;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Yajra\DataTables\Facades\DataTables;

class AuditLogsController extends BaseController implements HasMiddleware
{
    public function __construct(Audit $model)
    {
        parent::__construct(
            model: $model,
            module: 'audit_logs',
            displayNameSingular: 'Audit Log',
            displayNamePlural: 'Audit Logs',
            fieldDefs: [],
        );

        $this->baseUrl = url('manage/audit-logs');
    }

    public static function middleware(): array
    {
        return [
            new Middleware(PermissionMiddleware::using(PermissionsEnum::BROWSE_AUDIT_LOGS->value), only: ['index', 'datatable']),
            new Middleware(PermissionMiddleware::using(PermissionsEnum::READ_AUDIT_LOGS->value), only: ['show', 'store']),
        ];
    }

    /**
     * Return list of resource consumed by datatables library
     */
    public function datatable(Request $request): JsonResponse
    {
        $user = Auth::user();
        $timezone = $user->timezone;

        return DataTables::eloquent($this->model::query())
            ->editColumn('user_id', function (Audit $audit) {
                return $audit->user ? $audit->user->email : 'N/A';
            })
            ->editColumn('old_values', function (Audit $audit) {
                return '<pre class="audit-values">' . json_encode($audit->old_values, JSON_PRETTY_PRINT, true) . '</pre>';
            })
            ->editColumn('new_values', function (Audit $audit) {
                return '<pre class="audit-values">' . json_encode($audit->new_values, JSON_PRETTY_PRINT, true) . '</pre>';
            })
            ->editColumn('created_at', function (Audit $order) use ($timezone) {
                $date = $order->created_at;

                if (!$date instanceof Carbon) {
                    $date = new Carbon($date);
                }

                $date->setTimezone($timezone);
                return $date->format('d-m-Y H:i:s');
            })
            ->editColumn('updated_at', function (Audit $order) use ($timezone) {
                $date = $order->created_at;

                if (!$date instanceof Carbon) {
                    $date = new Carbon($date);
                }

                $date->setTimezone($timezone);
                return $date->format('d-m-Y H:i:s');
            })
            ->escapeColumns([])
            ->toJson();
    }

    public function edit(string $id, array $dataToRender = []): \Illuminate\View\View
    {
        abort(403, 'Not implemented');
    }

    public function store(Request $request): JsonResponse
    {
        return response()->json(['status' => false, 'message' => 'Not implemented'], 403);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        return response()->json(['status' => false, 'message' => 'Not implemented'], 403);
    }

    public function destroy(string $id): JsonResponse
    {
        return response()->json(['status' => false, 'message' => 'Not implemented'], 403);
    }
}
