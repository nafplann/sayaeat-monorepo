<?php

namespace App\Http\Controllers;

use App\Core\AppFieldDef;
use App\Enums\InputType;
use App\Enums\MenuStatus;
use App\Enums\PermissionsEnum;
use App\Imports\MerchantMenusImport;
use App\Models\Menu;
use App\Models\Merchant;
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

class MenusController extends BaseController implements HasMiddleware
{
    public function __construct(Menu $model)
    {
        $user = Auth::user();
        $merchants = $user->merchants;

        if (!$user->isOwner()) {
            $merchants = Merchant::all();
        }

        $fieldDefs = [
            new AppFieldDef(column: 'id', label: 'ID', browsable: false, creatable: false, editable: false),
            new AppFieldDef(
                column: 'name',
                label: 'Name',
                validationRulesForAdding: ['required', 'min:3'],
                validationRulesForEditing: ['required', 'min:3'],
            ),
            new AppFieldDef(
                column: 'description',
                label: 'Description'
            ),
            new AppFieldDef(
                column: 'price',
                label: 'Price',
                inputType: InputType::NUMERIC,
                validationRulesForAdding: ['required', 'numeric'],
                validationRulesForEditing: ['required', 'numeric'],
            ),
            new AppFieldDef(
                column: 'status',
                label: 'Status',
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
                label: 'Menu Image',
                inputType: InputType::IMAGE,
                browsable: false,
                validationRulesForAdding: ['required', File::types(['png', 'jpg', 'jpeg'])->max('1mb')],
                validationRulesForEditing: [File::types(['png', 'jpg', 'jpeg'])->max('1mb')]
            ),
            new AppFieldDef(
                column: 'merchant_id',
                label: 'Merchant',
                inputType: InputType::SELECT,
                selectOptions: [
                    'options' => ViewUtil::toSelectOptions($merchants)
                ],
                validationRulesForAdding: ['required', 'exists:merchants,id'],
                validationRulesForEditing: ['required', 'exists:merchants,id'],
            ),
            new AppFieldDef(
                column: 'category_id',
                label: 'Category',
                inputType: InputType::SELECT,
                selectOptions: [
                    'default' => '',
                    'options' => []
                ],
                validationRulesForAdding: ['required'],
                validationRulesForEditing: ['required'],
            ),
            new AppFieldDef(column: 'created_at', label: 'Created at', creatable: false, editable: false, datatableClass: 'text-center'),
            new AppFieldDef(column: 'updated_at', label: 'Updated at', creatable: false, editable: false, datatableClass: 'text-center'),
        ];

        parent::__construct(
            model: $model,
            module: 'menus',
            displayNameSingular: 'Menu',
            displayNamePlural: 'Menus',
            fieldDefs: $fieldDefs,
        );
    }

    public static function middleware(): array
    {
        return [
            new Middleware(PermissionMiddleware::using(PermissionsEnum::BROWSE_MENUS->value), only: ['index', 'datatable']),
            new Middleware(PermissionMiddleware::using(PermissionsEnum::ADD_MENUS->value), only: ['create', 'store']),
            new Middleware(PermissionMiddleware::using(PermissionsEnum::EDIT_MENUS->value), only: ['edit', 'update']),
            new Middleware(PermissionMiddleware::using(PermissionsEnum::DELETE_MENUS->value), only: ['destroy']),
            new Middleware(PermissionMiddleware::using(PermissionsEnum::IMPORT_MENUS->value), only: ['import']),
        ];
    }

    /**
     * Return list of resource consumed by datatables library
     */
    public function datatable(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = $this->model::query();

        $merchantIds = request()->get('merchant_ids');

        if ($user->isOwner()) {
            $ownerMerchantIds = $user->merchants
                ->pluck('id')
                ->toArray();
            $appliedMerchantIds = $merchantIds ? array_intersect($merchantIds, $ownerMerchantIds) : $ownerMerchantIds;
            $query->whereIn('merchant_id', $appliedMerchantIds);
        } else {
            if ($merchantIds) {
                $query->whereIn('merchant_id', $merchantIds);
            }
        }

        return DataTables::eloquent($query)
            ->editColumn('category_id', function (Menu $menu) {
                return $menu->category ? $menu->category->name : '';
            })
            ->editColumn('price', function (Menu $menu) {
                return display_price($menu->price);
            })
            ->editColumn('status', function (Menu $menu) {
                return MenuStatus::from($menu->status)->name;
            })
            ->editColumn('merchant_id', function (Menu $menu) {
                return $menu->merchant_id ? $menu->merchant->name : '';
            })
            ->toJson();
    }

    /**
     * Return list of menu by merchant id
     */
    public function getByMerchant(Request $request, string $merchantId): JsonResponse
    {
        $merchant = Merchant::findOrFail($merchantId);

        Gate::authorize('view', $merchant);

        return response()->json($merchant->menus);
    }

    /**
     * Toggle menu status
     */
    public function toggleStatus(Request $request, string $menuId): JsonResponse
    {
        $status = $request->get('status') === 'true' ? 1 : 0;

        $menu = Menu::findOrFail($menuId);

        Gate::authorize('update', $menu);

        $menu->update([
            'status' => MenuStatus::from($status)->value,
        ]);

        return response()->json([]);
    }

    /**
     * Toggle all menu status under selected merchant
     */
    public function toggleAllStatus(Request $request, string $merchantIds): JsonResponse
    {
        $status = $request->get('status') === 'true' ? 1 : 0;

        $ids = explode(',', $merchantIds);

        foreach ($ids as $id) {
            $merchant = Merchant::findOrFail($id);
            Gate::authorize('update', $merchant);

            $merchant->menus()->update([
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
            Excel::import(new MerchantMenusImport($request->get('merchant')), request()->file('spreadsheet'));
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
            'message' => 'Menu import success'
        ]);
    }
}
