<?php

namespace App\Http\Controllers;

use App\Core\AppFieldDef;
use App\Enums\InputType;
use App\Enums\PermissionsEnum;
use App\Enums\TrueFalseEnum;
use App\Models\Archive;
use App\Models\ArchiveFile;
use App\Models\MenuCategory;
use App\Models\Merchant;
use App\Utils\ViewUtil;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Yajra\DataTables\Facades\DataTables;

class MenuCategoriesController extends BaseController implements HasMiddleware
{
    public function __construct(MenuCategory $model)
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
                label: 'Description',
            ),
            new AppFieldDef(
                column: 'enabled',
                label: 'Enabled',
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
                label: 'Sorting',
                validationRulesForAdding: ['required'],
                validationRulesForEditing: ['required'],
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
            new AppFieldDef(column: 'created_at', label: 'Created at', creatable: false, editable: false, datatableClass: 'text-center'),
            new AppFieldDef(column: 'updated_at', label: 'Updated at', creatable: false, editable: false, datatableClass: 'text-center'),
        ];

        parent::__construct(
            model: $model,
            module: 'menu-categories',
            displayNameSingular: 'Menu Category',
            displayNamePlural: 'Menu Categories',
            fieldDefs: $fieldDefs,
        );
    }

    public static function middleware(): array
    {
        return [
            new Middleware(PermissionMiddleware::using(PermissionsEnum::BROWSE_MENU_CATEGORIES->value), only: ['index', 'datatable']),
            new Middleware(PermissionMiddleware::using(PermissionsEnum::ADD_MENU_CATEGORIES->value), only: ['create', 'store']),
            new Middleware(PermissionMiddleware::using(PermissionsEnum::EDIT_MENU_CATEGORIES->value), only: ['edit', 'update']),
            new Middleware(PermissionMiddleware::using(PermissionsEnum::DELETE_MENU_CATEGORIES->value), only: ['destroy']),
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
            ->editColumn('merchant_id', function (MenuCategory $category) {
                return $category->merchant_id ? $category->merchant->name : '';
            })
            ->editColumn('enabled', function (MenuCategory $category) {
                return $category->enabled === 1 ? 'YES' : 'NO';
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

        return response()->json($merchant->menu_categories);
    }
}
