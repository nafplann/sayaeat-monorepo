<?php

namespace App\Http\Controllers;

use App\Core\AppFieldDef;
use App\Enums\InputType;
use App\Enums\MarketAja\DiscountType;
use App\Enums\PermissionsEnum;
use App\Models\ProductDiscount;
use App\Models\Store;
use App\Utils\ViewUtil;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Yajra\DataTables\Facades\DataTables;

class ProductDiscountsController extends BaseController implements HasMiddleware
{
    public function __construct(ProductDiscount $model)
    {
        $user = Auth::user();
        $stores = $user->stores;

        if (!$user->isOwner()) {
            $stores = Store::all();
        }

        $fieldDefs = [
            new AppFieldDef(column: 'id', label: 'ID', browsable: false, creatable: false, editable: false),
            new AppFieldDef(
                column: 'name',
                label: __('app.name'),
                validationRulesForAdding: ['required', 'min:3'],
                validationRulesForEditing: ['required', 'min:3'],
            ),
            new AppFieldDef(
                column: 'description',
                label: __('app.description'),
                browsable: false,
            ),
            new AppFieldDef(
                column: 'discount_type',
                label: __('app.discount_type'),
                inputType: InputType::SELECT,
                selectOptions: [
                    'default' => DiscountType::FIXED,
                    'options' => DiscountType::toSelectOptions()
                ],
            ),
            new AppFieldDef(
                column: 'discount_amount',
                label: __('app.discount_amount'),
                inputType: InputType::NUMERIC
            ),
            new AppFieldDef(
                column: 'discount_percentage',
                label: __('app.discount_percentage'),
                inputType: InputType::NUMBER,
                inputAttributes: [
                    'min' => 1,
                    'max' => 100
                ]
            ),
            new AppFieldDef(
                column: 'start_date',
                label: __('app.start_date'),
                inputType: InputType::DATETIME,
                validationRulesForAdding: ['required', 'date'],
                validationRulesForEditing: ['required', 'date'],
            ),
            new AppFieldDef(
                column: 'end_date',
                label: __('app.end_date'),
                inputType: InputType::DATETIME,
                validationRulesForAdding: ['required', 'date', 'after:start_date'],
                validationRulesForEditing: ['required', 'date', 'after:start_date'],
            ),
            new AppFieldDef(
                column: 'store_id',
                label: __('app.store'),
                inputType: InputType::SELECT,
                selectOptions: [
                    'options' => ViewUtil::toSelectOptions($stores)
                ],
                validationRulesForAdding: ['required', 'exists:stores,id'],
                validationRulesForEditing: ['required', 'exists:stores,id'],
            ),
            new AppFieldDef(column: 'created_at', label: 'Created at', creatable: false, editable: false, datatableClass: 'text-center'),
            new AppFieldDef(column: 'updated_at', label: 'Updated at', creatable: false, editable: false, datatableClass: 'text-center'),
        ];

        parent::__construct(
            model: $model,
            module: 'product-discounts',
            displayNameSingular: __('app.discount'),
            displayNamePlural: __('app.discounts'),
            fieldDefs: $fieldDefs,
        );
    }

    public static function middleware(): array
    {
        return [
            new Middleware(PermissionMiddleware::using(PermissionsEnum::BROWSE_PRODUCT_DISCOUNTS->value), only: ['index', 'datatable']),
            new Middleware(PermissionMiddleware::using(PermissionsEnum::ADD_PRODUCT_DISCOUNTS->value), only: ['create', 'store']),
            new Middleware(PermissionMiddleware::using(PermissionsEnum::EDIT_PRODUCT_DISCOUNTS->value), only: ['edit', 'update']),
            new Middleware(PermissionMiddleware::using(PermissionsEnum::DELETE_PRODUCT_DISCOUNTS->value), only: ['destroy']),
        ];
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate(array_merge(
            $this->getValidationRulesForAdding(),
            [
                "product_to_link" => ['required', 'array'],
                "product_to_link.*" => ['required', 'string'],
            ]
        ));

        try {
            DB::beginTransaction();

            $discount = $this->model::create(
                $request->only($this->getCreatableColumn())
            );

            $discount->products()->sync($request->get('product_to_link'));

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }

        return response()->json(['status' => true, 'message' => "{$this->displayNameSingular} has been added successfully."]);
    }

    /**
     * Return list of resource consumed by datatables library
     */
    public function datatable(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = $this->model::query();

        if ($user->isOwner()) {
            $ownerStoreIds = $user->stores
                ->pluck('id')
                ->toArray();

            $query->whereIn('store_id', $ownerStoreIds);
        }

        return DataTables::eloquent($query)
            ->editColumn('store_id', function (ProductDiscount $discount) {
                return $discount->store_id ? $discount->store->name : '';
            })
            ->toJson();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate(array_merge(
            $this->getValidationRulesForEditing(),
            [
                "product_to_link" => ['required', 'array'],
                "product_to_link.*" => ['required', 'string'],
            ]
        ));

        $discount = $this->model::findOrFail($id);

        try {
            DB::beginTransaction();

            $discount->update(
                $request->only($this->getEditableColumn())
            );

            $discount->products()->sync($request->get('product_to_link'));

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }

        return response()->json(['status' => true, 'message' => "{$this->displayNameSingular} has been updated successfully."]);
    }
}
