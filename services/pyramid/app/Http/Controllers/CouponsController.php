<?php

namespace App\Http\Controllers;

use App\Core\AppFieldDef;
use App\Enums\InputType;
use App\Enums\PermissionsEnum;
use App\Models\Coupon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Yajra\DataTables\Facades\DataTables;

class CouponsController extends BaseController implements HasMiddleware
{
    public function __construct(Coupon $model)
    {
        $fieldDefs = [
            new AppFieldDef(column: 'id', label: 'ID', browsable: false, creatable: false, editable: false),
            new AppFieldDef(
                column: 'code',
                label: 'Code',
                validationRulesForAdding: ['required'],
            ),
            new AppFieldDef(
                column: 'description',
                label: 'Description',
            ),
            new AppFieldDef(
                column: 'max_per_customer',
                label: 'Max per Customer',
                validationRulesForAdding: ['required', 'min:1', 'max:1000000'],
                validationRulesForEditing: ['required', 'min:1', 'max:1000000'],
            ),
            new AppFieldDef(
                column: 'total_quantity',
                label: 'Total Quantity',
                validationRulesForAdding: ['required', 'min:1', 'max:1000000'],
                validationRulesForEditing: ['required', 'min:1', 'max:1000000'],
            ),
            new AppFieldDef(
                column: 'redeemed_quantity',
                label: 'Redeem Quantity',
                validationRulesForAdding: ['required', 'min:1', 'max:1000000'],
                validationRulesForEditing: ['required', 'min:1', 'max:1000000'],
            ),
            new AppFieldDef(
                column: 'minimum_purchase',
                label: 'Minimum Purchase',
            ),
            new AppFieldDef(
                column: 'discount_amount',
                label: 'Discount Amount',
            ),
            new AppFieldDef(
                column: 'discount_percentage',
                label: 'Discount Percentage',
                inputType: InputType::NUMBER,
                inputAttributes: [
                    'min' => 1,
                    'max' => 100
                ]
            ),
            new AppFieldDef(
                column: 'max_discount_amount',
                label: 'Maximum Discount Amount',
            ),
            new AppFieldDef(
                column: 'valid_from',
                label: 'Valid From',
                inputType: InputType::DATETIME,
                validationRulesForAdding: ['required', 'date'],
                validationRulesForEditing: ['required', 'date'],
            ),
            new AppFieldDef(
                column: 'valid_until',
                label: 'Valid Until',
                inputType: InputType::DATETIME,
                validationRulesForAdding: ['required', 'date', 'after:start_date'],
                validationRulesForEditing: ['required', 'date', 'after:start_date'],
            ),
            new AppFieldDef(
                column: 'is_platform_promotion',
                inputType: InputType::HIDDEN,
                browsable: false
            ),
            new AppFieldDef(
                column: 'is_enabled',
                inputType: InputType::HIDDEN,
                browsable: false
            ),
            new AppFieldDef(column: 'created_at', label: 'Created at', creatable: false, editable: false, datatableClass: 'text-center'),
            new AppFieldDef(column: 'updated_at', label: 'Updated at', creatable: false, editable: false, datatableClass: 'text-center'),
        ];

        parent::__construct(
            model: $model,
            module: 'coupons',
            displayNameSingular: __('app.coupon'),
            displayNamePlural: __('app.coupons'),
            fieldDefs: $fieldDefs,
        );
    }

    public static function middleware(): array
    {
        return [
            new Middleware(PermissionMiddleware::using(PermissionsEnum::BROWSE_COUPONS->value), only: ['index', 'datatable']),
            new Middleware(PermissionMiddleware::using(PermissionsEnum::ADD_COUPONS->value), only: ['create', 'store']),
            new Middleware(PermissionMiddleware::using(PermissionsEnum::EDIT_COUPONS->value), only: ['edit', 'update']),
            new Middleware(PermissionMiddleware::using(PermissionsEnum::DELETE_COUPONS->value), only: ['destroy']),
        ];
    }

    public function store(Request $request): JsonResponse
    {
        $request->request->set('is_platform_promotion', 1);
        $request->request->set('is_enabled', 1);

        return parent::store($request);
    }


    /**
     * Return list of resource consumed by datatables library
     */
    public function datatable(Request $request): JsonResponse
    {
        return DataTables::eloquent($this->model::query())
//            ->editColumn('employment_status', function (Driver $driver) {
//                return EmploymentStatus::from($driver->employment_status)->name;
//            })
            ->toJson();
    }
}
