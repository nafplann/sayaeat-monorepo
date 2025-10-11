<?php

namespace App\Http\Controllers;

use App\Core\AppFieldDef;
use App\Enums\InputType;
use App\Enums\PermissionsEnum;
use App\Enums\TrueFalseEnum;
use App\Models\MenuAddon;
use App\Models\MenuAddonCategory;
use App\Models\Merchant;
use App\Utils\ViewUtil;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Yajra\DataTables\Facades\DataTables;

class MenuAddonCategoriesController extends BaseController implements HasMiddleware
{
    public function __construct(MenuAddonCategory $model)
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
                column: 'is_mandatory',
                label: 'Mandatory?',
                inputType: InputType::SELECT,
                selectOptions: [
                    'default' => TrueFalseEnum::YES,
                    'options' => TrueFalseEnum::toSelectOptions()
                ],
                validationRulesForAdding: ['required'],
                validationRulesForEditing: ['required'],
            ),
            new AppFieldDef(
                column: 'max_selection',
                label: 'Maximum Selection',
                inputType: InputType::NUMBER,
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
            module: 'menu-addon-categories',
            displayNameSingular: 'Menu Addon Category',
            displayNamePlural: 'Menu Addon Categories',
            fieldDefs: $fieldDefs,
        );
    }

    public static function middleware(): array
    {
        return [
            new Middleware(PermissionMiddleware::using(PermissionsEnum::BROWSE_ADDON_CATEGORIES->value), only: ['index', 'datatable']),
            new Middleware(PermissionMiddleware::using(PermissionsEnum::ADD_ADDON_CATEGORIES->value), only: ['create', 'store']),
            new Middleware(PermissionMiddleware::using(PermissionsEnum::EDIT_ADDON_CATEGORIES->value), only: ['edit', 'update']),
            new Middleware(PermissionMiddleware::using(PermissionsEnum::DELETE_ADDON_CATEGORIES->value), only: ['destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('menu_addon_categories.browse', [
            'module' => $this->displayNamePlural,
            'baseUrl' => $this->baseUrl,
            'fieldDefs' => $this->fieldDefs,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate(array_merge(
            $this->getValidationRulesForAdding(),
            [
                "addon_name" => ['required', 'array'],
                "addon_name.*" => ['required', 'string'],
                "addon_price" => ['required', 'array'],
                "addon_price.*" => ['required', 'numeric'],
                "menu_to_link" => ['required', 'array'],
                "menu_to_link.*" => ['required', 'string'],
            ]
        ));

        try {
            DB::beginTransaction();

            $category = $this->model::create(
                $request->only($this->getCreatableColumn())
            );

            foreach ($request->get('addon_name') as $index => $name) {
                MenuAddon::create([
                    'name' => $name,
                    'price' => $request->get('addon_price')[$index],
                    'enabled' => 1,
                    'category_id' => $category->id
                ]);
            }

            $category->menus()->sync($request->get('menu_to_link'));

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }

        return response()->json(['status' => true, 'message' => "{$this->displayNameSingular} has been added successfully."]);
    }

    /**
     * Show the form for creating a new resource.
     * @param array $dataToRender
     * @return View
     */
    public function create(array $dataToRender = []): View
    {
        return view('menu_addon_categories.add_edit', array_merge([
            'module' => $this->displayNameSingular,
            'baseUrl' => $this->baseUrl,
            'fieldDefs' => $this->fieldDefs,
        ], $dataToRender));
    }

    /**
     * Show the form for editing the specified resource.
     * @param string $id
     * @param array $dataToRender
     * @return View
     */
    public function edit(string $id, array $dataToRender = []): View
    {
        $data = $this->model::findOrFail($id);

        return view('menu_addon_categories.add_edit', array_merge([
            'data' => $data,
            'module' => $this->displayNameSingular,
            'baseUrl' => $this->baseUrl,
            'fieldDefs' => $this->fieldDefs,
        ], [
            ...$dataToRender,

        ]));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate(array_merge(
            $this->getValidationRulesForEditing(),
            [
                "addon_name" => ['required', 'array'],
                "addon_name.*" => ['required', 'string'],
                "addon_price" => ['required', 'array'],
                "addon_price.*" => ['required', 'numeric'],
                "menu_to_link" => ['required', 'array'],
                "menu_to_link.*" => ['required', 'string'],
            ]
        ));

        $category = $this->model::findOrFail($id);

        try {
            DB::beginTransaction();

            $category->update(
                $request->only($this->getEditableColumn())
            );

            foreach ($request->get('addon_name') as $index => $name) {
                $addon = MenuAddon::find($index);
                $addonData = [
                    'name' => $name,
                    'price' => $request->get('addon_price')[$index],
                ];

                if ($addon === null) {
                    MenuAddon::create([
                        ...$addonData,
                        'status' => 1,
                        'category_id' => $id
                    ]);
                } else {
                    $addon->update($addonData);
                }
            }

            $category->menus()->sync($request->get('menu_to_link'));

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }

        return response()->json(['status' => true, 'message' => "{$this->displayNameSingular} has been updated successfully."]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $data = $this->model::findOrFail($id);

        try {
            $data->delete();
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }

        return response()->json(['status' => true, 'message' => "{$this->displayNameSingular} has been deleted successfully."]);
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
            ->editColumn('is_mandatory', function (MenuAddonCategory $addonCategory) {
                return TrueFalseEnum::from($addonCategory->is_mandatory)->name;
            })
            ->editColumn('merchant_id', function (MenuAddonCategory $addonCategory) {
                return $addonCategory->merchant_id ? $addonCategory->merchant->name : '';
            })
            ->toJson();
    }

    /**
     * Remove addon from category
     */
    public function addonDelete(Request $request, string $id)
    {
        $file = MenuAddon::findOrFail($id);

        try {
            $file->delete();
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }

        return response()->json(['status' => true, 'message' => "Addon has been deleted successfully."]);
    }
}
