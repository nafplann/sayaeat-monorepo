<?php

namespace App\Http\Controllers;

use App\Core\AppFieldDef;
use App\Enums\InputType;
use App\Enums\MenuStatus;
use App\Enums\PermissionsEnum;
use App\Enums\ProductUnits;
use App\Imports\StoreProductsImport;
use App\Models\Product;
use App\Models\Store;
use App\Utils\DateUtil;
use App\Utils\ViewUtil;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\File;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Yajra\DataTables\Facades\DataTables;

class ProductsController extends BaseController implements HasMiddleware
{
    public function __construct(Product $model)
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
                label: __('app.product_name'),
                validationRulesForAdding: ['required', 'min:3'],
                validationRulesForEditing: ['required', 'min:3'],
            ),
            new AppFieldDef(
                column: 'description',
                label: __('app.description'),
                browsable: false
            ),
            new AppFieldDef(
                column: 'sku',
                label: 'SKU',
            ),
            new AppFieldDef(
                column: 'barcode',
                label: 'Barcode',
            ),
            new AppFieldDef(
                column: 'price',
                label: __('app.price'),
                inputType: InputType::NUMERIC,
                validationRulesForAdding: ['required', 'numeric'],
                validationRulesForEditing: ['required', 'numeric'],
            ),
            new AppFieldDef(
                column: 'unit',
                label: __('app.product_unit'),
                inputType: InputType::SELECT,
                selectOptions: [
                    'default' => ProductUnits::PCS->name,
                    'options' => collect(ProductUnits::cases())->reduce(function ($prev, $item) {
                        $prev[$item->name] = $item->value;
                        return $prev;
                    }, [])
                ],
                validationRulesForAdding: ['required'],
                validationRulesForEditing: ['required'],
            ),
            new AppFieldDef(
                column: 'minimum_purchase_quantity',
                label: __('app.minimum_quantity'),
                inputType: InputType::NUMERIC,
                browsable: false,
                validationRulesForAdding: ['required', 'numeric'],
                validationRulesForEditing: ['required', 'numeric'],
            ),
            new AppFieldDef(
                column: 'status',
                label: __('app.status'),
                inputType: InputType::SELECT,
                selectOptions: [
                    'default' => MenuStatus::AVAILABLE->name,
                    'options' => collect(MenuStatus::cases())->reduce(function ($prev, $item) {
                        $prev[$item->name] = $item->value;
                        return $prev;
                    }, [])
                ],
                columnOrder: 0,
                validationRulesForAdding: ['required'],
                validationRulesForEditing: ['required'],
            ),
            new AppFieldDef(
                column: 'image_path',
                label: __('app.product_image'),
                inputType: InputType::IMAGE,
                browsable: false,
                validationRulesForAdding: ['required', File::types(['png', 'jpg', 'jpeg'])->max('1mb')],
                validationRulesForEditing: [File::types(['png', 'jpg', 'jpeg'])->max('1mb')]
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
            new AppFieldDef(
                column: 'categories',
                label: __('app.category'),
                inputType: InputType::SELECT_MULTIPLE,
                selectOptions: [
                    'default' => '',
                    'options' => []
                ],
                browsable: false,
                validationRulesForAdding: ['required'],
                validationRulesForEditing: ['required'],
            ),
            new AppFieldDef(column: 'created_at', label: __('app.created_at'), creatable: false, editable: false, datatableClass: 'text-center'),
            new AppFieldDef(column: 'updated_at', label: __('app.updated_at'), creatable: false, editable: false, datatableClass: 'text-center'),
        ];

        parent::__construct(
            model: $model,
            module: 'products',
            displayNameSingular: __('app.product'),
            displayNamePlural: __('app.products'),
            fieldDefs: $fieldDefs,
        );
    }

    public static function middleware(): array
    {
        return [
            new Middleware(PermissionMiddleware::using(PermissionsEnum::BROWSE_PRODUCTS->value), only: ['index', 'datatable']),
            new Middleware(PermissionMiddleware::using(PermissionsEnum::ADD_PRODUCTS->value), only: ['create', 'store']),
            new Middleware(PermissionMiddleware::using(PermissionsEnum::EDIT_PRODUCTS->value), only: ['edit', 'update']),
            new Middleware(PermissionMiddleware::using(PermissionsEnum::DELETE_PRODUCTS->value), only: ['destroy']),
            new Middleware(PermissionMiddleware::using(PermissionsEnum::IMPORT_PRODUCTS->value), only: ['import']),
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
            $appliedMerchantIds = $storeIds ? array_intersect($storeIds, $ownerStoreIds) : $ownerStoreIds;
            $query->whereIn('store_id', $appliedMerchantIds);
        } else {
            if ($storeIds) {
                $query->whereIn('store_id', $storeIds);
            }
        }

        return DataTables::eloquent($query)
            ->editColumn('categories', function (Product $product) {
                return $product->categories
                    ? $product->categories->pluck('name')->join(', ')
                    : '';
            })
            ->editColumn('price', function (Product $product) {
                return display_price($product->price);
            })
            ->editColumn('status', function (Product $product) {
                return MenuStatus::from($product->status)->name;
            })
            ->editColumn('store_id', function (Product $product) {
                return $product->store_id ? $product->store->name : '';
            })
            ->editColumn('created_at', function (Product $product) use ($timezone) {
                return DateUtil::toUserLocalTime($product->created_at, $timezone);
            })
            ->editColumn('updated_at', function (Product $product) use ($timezone) {
                return DateUtil::toUserLocalTime($product->updated_at, $timezone);
            })
            ->toJson();
    }

    /**
     * Return list of menu by merchant id
     */
    public function getByStore(Request $request, string $storeId): JsonResponse
    {
        $store = Store::findOrFail($storeId);

        Gate::authorize('view', $store);

        return response()->json($store->products);
    }

    /**
     * Toggle menu status
     */
    public function toggleStatus(Request $request, string $productId): JsonResponse
    {
        $status = $request->get('status') === 'true' ? 1 : 0;

        $product = Product::findOrFail($productId);

        Gate::authorize('update', $product);

        $product->update([
            'status' => MenuStatus::from($status)->value,
        ]);

        return response()->json([]);
    }

    /**
     * Toggle all menu status under selected merchant
     */
    public function toggleAllStatus(Request $request, string $storeIds): JsonResponse
    {
        $status = $request->get('status') === 'true' ? 1 : 0;

        $ids = explode(',', $storeIds);

        foreach ($ids as $id) {
            $store = Store::findOrFail($id);
            Gate::authorize('update', $store);

            $store->products()->update([
                'status' => MenuStatus::from($status)->value,
            ]);
        }

        return response()->json([]);
    }

    /**
     * Menu import from spreadsheet
     */
    public function import(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            Excel::import(new StoreProductsImport($request->get('store')), request()->file('spreadsheet'));
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 400);
        }

        return response()->json([
            'status' => true,
            'message' => 'Product import success'
        ]);
    }
}
