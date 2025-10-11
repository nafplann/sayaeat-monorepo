<?php

namespace App\Http\Controllers;

use App\Core\AppFieldDef;
use App\Enums\InputType;
use App\Enums\PermissionsEnum;
use App\Enums\TrueFalseEnum;
use App\Models\ProductCategory;
use App\Models\Store;
use App\Utils\DateUtil;
use App\Utils\ViewUtil;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Yajra\DataTables\Facades\DataTables;

class ProductCategoriesController extends BaseController implements HasMiddleware
{
    public function __construct(ProductCategory $model)
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
                label: __('app.category'),
                validationRulesForAdding: ['required', 'min:3'],
                validationRulesForEditing: ['required', 'min:3'],
            ),
            new AppFieldDef(
                column: 'description',
                label: __('app.description'),
            ),
            new AppFieldDef(
                column: 'enabled',
                label: __('app.enabled'),
                inputType: InputType::SELECT,
                selectOptions: [
                    'default' => TrueFalseEnum::YES,
                    'options' => TrueFalseEnum::toSelectOptions()
                ],
                validationRulesForAdding: ['required'],
                validationRulesForEditing: ['required'],
            ),
            new AppFieldDef(
                column: 'sorting',
                label: __('app.sorting'),
                validationRulesForAdding: ['required'],
                validationRulesForEditing: ['required'],
            ),
            new AppFieldDef(
                column: 'store_id',
                label: __('app.store_name'),
                inputType: InputType::SELECT,
                selectOptions: [
                    'options' => ViewUtil::toSelectOptions($stores)
                ],
                validationRulesForAdding: ['required', 'exists:stores,id'],
                validationRulesForEditing: ['required', 'exists:stores,id'],
            ),
            new AppFieldDef(column: 'created_at', label: __('app.created_at'), creatable: false, editable: false, datatableClass: 'text-center'),
            new AppFieldDef(column: 'updated_at', label: __('app.updated_at'), creatable: false, editable: false, datatableClass: 'text-center'),
        ];

        parent::__construct(
            model: $model,
            module: 'product-categories',
            displayNameSingular: __('app.product_category'),
            displayNamePlural: __('app.product_categories'),
            fieldDefs: $fieldDefs,
        );
    }

    public static function middleware(): array
    {
        return [
            new Middleware(PermissionMiddleware::using(PermissionsEnum::BROWSE_PRODUCT_CATEGORIES->value), only: ['index', 'datatable']),
            new Middleware(PermissionMiddleware::using(PermissionsEnum::ADD_PRODUCT_CATEGORIES->value), only: ['create', 'store']),
            new Middleware(PermissionMiddleware::using(PermissionsEnum::EDIT_PRODUCT_CATEGORIES->value), only: ['edit', 'update']),
            new Middleware(PermissionMiddleware::using(PermissionsEnum::DELETE_PRODUCT_CATEGORIES->value), only: ['destroy']),
        ];
    }

    /**
     * Return list of resource consumed by datatables library
     */
    public function datatable(Request $request): JsonResponse
    {
        $user = Auth::user();
        $timezone = $user->timezone;
        $query = $this->model::query();

        $storeIds = $request->get('store_ids');

        if ($user->isOwner()) {
            $ownerStoreIds = $user->stores
                ->pluck('id')
                ->toArray();
            $appliedStoreIds = $storeIds ? array_intersect($storeIds, $ownerStoreIds) : $ownerStoreIds;
            $query->whereIn('store_id', $appliedStoreIds);
        } else {
            if ($storeIds) {
                $query->whereIn('store_id', $storeIds);
            }
        }

        return DataTables::eloquent($query)
            ->editColumn('store_id', function (ProductCategory $category) {
                return $category->store_id ? $category->store->name : '';
            })
            ->editColumn('enabled', function (ProductCategory $category) {
                return $category->enabled === 1 ? 'YES' : 'NO';
            })
            ->editColumn('created_at', function (ProductCategory $store) use ($timezone) {
                return DateUtil::toUserLocalTime($store->created_at, $timezone);
            })
            ->editColumn('updated_at', function (ProductCategory $store) use ($timezone) {
                return DateUtil::toUserLocalTime($store->updated_at, $timezone);
            })
            ->toJson();
    }

    /**
     * Return list of menu by merchant id
     */
    public function getByMerchant(Request $request, string $storeId): JsonResponse
    {
        $store = Store::findOrFail($storeId);

        Gate::authorize('view', $store);

        return response()->json($store->product_categories);
    }
}
