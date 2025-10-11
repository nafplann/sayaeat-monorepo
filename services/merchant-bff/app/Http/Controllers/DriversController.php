<?php

namespace App\Http\Controllers;

use App\Core\AppFieldDef;
use App\Enums\EmploymentStatus;
use App\Enums\InputType;
use App\Enums\PermissionsEnum;
use App\Models\Driver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Yajra\DataTables\Facades\DataTables;

class DriversController extends BaseController implements HasMiddleware
{
    public function __construct(Driver $model)
    {
        $fieldDefs = [
            new AppFieldDef(column: 'id', label: 'ID', browsable: false, creatable: false, editable: false),
            new AppFieldDef(
                column: 'code',
                label: 'Code',
                validationRulesForAdding: ['required'],
            ),
            new AppFieldDef(
                column: 'name',
                label: 'Name',
                validationRulesForAdding: ['required', 'min:3'],
                validationRulesForEditing: ['required', 'min:3'],
            ),
            new AppFieldDef(
                column: 'phone_number',
                label: 'Phone Number',
                validationRulesForAdding: ['required', 'starts_with:+62', 'regex:/^\S*$/u'],
                validationRulesForEditing: ['required', 'starts_with:+62', 'regex:/^\S*$/u'],
            ),
            new AppFieldDef(
                column: 'address',
                label: 'Address',
                browsable: false,
                validationRulesForAdding: ['required'],
                validationRulesForEditing: ['required'],
            ),
            new AppFieldDef(
                column: 'employment_status',
                label: 'Employment Status',
                inputType: InputType::SELECT,
                selectOptions: [
                    'default' => 'Full-time',
                    'options' => [
                        'Full-time' => 2,
                        'Part-time' => 1,
                    ]
                ],
                validationRulesForAdding: ['required'],
                validationRulesForEditing: ['required'],
            ),
            new AppFieldDef(
                column: 'vehicle_model',
                label: 'Vehicle Model',
                validationRulesForAdding: ['required'],
                validationRulesForEditing: ['required'],
            ),
            new AppFieldDef(
                column: 'plate_number',
                label: 'Plate Number',
                validationRulesForAdding: ['required'],
                validationRulesForEditing: ['required'],
            ),
            new AppFieldDef(
                column: 'map_link',
                label: 'Map Link',
                browsable: false,
            ),
            new AppFieldDef(
                column: 'bank_name',
                label: 'Bank Name',
                browsable: false,
            ),
            new AppFieldDef(
                column: 'bank_account_holder',
                label: 'Bank Account Holder',
                browsable: false,
            ),
            new AppFieldDef(
                column: 'bank_account_number',
                label: 'Bank Account Number',
                browsable: false,
            ),
            new AppFieldDef(
                column: 'photo_path',
                label: 'Photo',
                inputType: InputType::IMAGE,
                browsable: false,
            ),
            new AppFieldDef(column: 'created_at', label: 'Created at', creatable: false, editable: false, datatableClass: 'text-center'),
            new AppFieldDef(column: 'updated_at', label: 'Updated at', creatable: false, editable: false, datatableClass: 'text-center'),
        ];

        parent::__construct(
            model: $model,
            module: 'drivers',
            displayNameSingular: 'Driver',
            displayNamePlural: 'Drivers',
            fieldDefs: $fieldDefs,
        );
    }

    public static function middleware(): array
    {
        return [
            new Middleware(PermissionMiddleware::using(PermissionsEnum::BROWSE_DRIVERS->value), only: ['index', 'datatable']),
            new Middleware(PermissionMiddleware::using(PermissionsEnum::ADD_DRIVERS->value), only: ['create', 'store']),
            new Middleware(PermissionMiddleware::using(PermissionsEnum::EDIT_DRIVERS->value), only: ['edit', 'update']),
            new Middleware(PermissionMiddleware::using(PermissionsEnum::DELETE_DRIVERS->value), only: ['destroy']),
        ];
    }

    /**
     * Return list of resource consumed by datatables library
     */
    public function datatable(Request $request): JsonResponse
    {
        return DataTables::eloquent($this->model::query())
            ->editColumn('employment_status', function (Driver $driver) {
                return EmploymentStatus::from($driver->employment_status)->name;
            })
            ->toJson();
    }
}
