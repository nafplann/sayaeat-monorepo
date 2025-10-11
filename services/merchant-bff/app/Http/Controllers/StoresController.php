<?php

namespace App\Http\Controllers;

use App\Core\AppFieldDef;
use App\Enums\InputType;
use App\Enums\PermissionsEnum;
use App\Enums\StoreCategory;
use App\Enums\StoreStatus;
use App\Models\Store;
use App\Models\User;
use App\Rules\OperatingHour;
use App\Utils\DateUtil;
use App\Utils\ViewUtil;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\File;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Yajra\DataTables\Facades\DataTables;

class StoresController extends BaseController implements HasMiddleware
{
    public function __construct(Store $model)
    {
        $fieldDefs = [
            new AppFieldDef(column: 'id', label: 'ID', browsable: false, creatable: false, editable: false),
            new AppFieldDef(
                column: 'name',
                label: __('app.store_name'),
                validationRulesForAdding: ['required', 'min:3'],
                validationRulesForEditing: ['required', 'min:3'],
            ),
            new AppFieldDef(
                column: 'description',
                label: __('app.description'),
                browsable: false,
            ),
            new AppFieldDef(
                column: 'category',
                label: __('app.category'),
                inputType: InputType::SELECT,
                selectOptions: [
                    'default' => 'SUPERMARKET',
                    'options' => []
                ],
                validationRulesForAdding: ['required'],
                validationRulesForEditing: ['required'],
            ),
            new AppFieldDef(
                column: 'address',
                label: __('app.address'),
                browsable: false,
                validationRulesForAdding: ['required'],
                validationRulesForEditing: ['required'],
            ),
            new AppFieldDef(
                column: 'phone_number',
                label: __('app.phone_number'),
                validationRulesForAdding: ['required', 'starts_with:+62', 'regex:/^\S*$/u'],
                validationRulesForEditing: ['required', 'starts_with:+62', 'regex:/^\S*$/u'],
            ),
            new AppFieldDef(
                column: 'primary_whatsapp_number',
                label: 'Whatsapp 1',
                browsable: false,
                validationRulesForAdding: ['starts_with:+62', 'regex:/^\S*$/u', 'nullable'],
                validationRulesForEditing: ['starts_with:+62', 'regex:/^\S*$/u', 'nullable'],
            ),
            new AppFieldDef(
                column: 'secondary_whatsapp_number',
                label: 'Whatsapp 2',
                browsable: false,
                validationRulesForAdding: ['starts_with:+62', 'regex:/^\S*$/u', 'nullable'],
                validationRulesForEditing: ['starts_with:+62', 'regex:/^\S*$/u', 'nullable'],
            ),
            new AppFieldDef(
                column: 'bank_name',
                label: __('app.bank_name'),
                browsable: false,
            ),
            new AppFieldDef(
                column: 'bank_account_holder',
                label: __('app.bank_account_holder'),
                browsable: false,
            ),
            new AppFieldDef(
                column: 'bank_account_number',
                label: __('app.bank_account_number'),
                browsable: false,
            ),
            new AppFieldDef(
                column: 'qris_link',
                label: 'QRIS Link',
                browsable: false,
            ),
            new AppFieldDef(
                column: 'latitude',
                label: 'Latitude',
                validationRulesForAdding: ['required'],
                validationRulesForEditing: ['required'],
            ),
            new AppFieldDef(
                column: 'longitude',
                label: 'Longitude',
                validationRulesForAdding: ['required'],
                validationRulesForEditing: ['required'],
            ),
            new AppFieldDef(
                column: 'status',
                label: __('app.status'),
                inputType: InputType::SELECT,
                selectOptions: [
                    'default' => 'OPEN',
                    'options' => [
                        'CLOSED' => 0,
                        'OPEN' => 1,
                    ]
                ],
                columnOrder: 0,
                validationRulesForAdding: ['required'],
                validationRulesForEditing: ['required'],
            ),
            new AppFieldDef(
                column: 'logo_path',
                label: __('app.logo'),
                inputType: InputType::IMAGE,
                browsable: false,
                validationRulesForAdding: ['required', File::types(['png', 'jpg', 'jpeg'])->max('1mb')],
                validationRulesForEditing: [File::types(['png', 'jpg', 'jpeg'])->max('1mb')],
            ),
            new AppFieldDef(
                column: 'banner_image',
                label: __('app.banner_image'),
                inputType: InputType::IMAGE,
                browsable: false,
                validationRulesForAdding: ['required', File::types(['png', 'jpg', 'jpeg'])->max('1mb')],
                validationRulesForEditing: [File::types(['png', 'jpg', 'jpeg'])->max('1mb')],
            ),
            new AppFieldDef(
                column: 'owner_id',
                label: __('app.owner'),
                inputType: InputType::SELECT,
                selectOptions: [
                    'options' => ViewUtil::toSelectOptions(User::all())
                ],
            ),
            new AppFieldDef(
                column: 'slug',
                label: 'Slug/Alias',
                editable: false,
                validationRulesForAdding: ['required', 'unique:stores'],
            ),
            new AppFieldDef(column: 'operating_hours', label: __('app.operating_hours'), inputType: InputType::HIDDEN, browsable: false,),
            new AppFieldDef(column: 'created_at', label: __('app.created_at'), creatable: false, editable: false, datatableClass: 'text-center'),
            new AppFieldDef(column: 'updated_at', label: __('app.updated_at'), creatable: false, editable: false, datatableClass: 'text-center'),
        ];

        parent::__construct(
            model: $model,
            module: 'stores',
            baseUrl: url('manage/stores'),
            displayNameSingular: __('app.stores'),
            displayNamePlural: __('app.my_stores'),
            fieldDefs: $fieldDefs,
        );
    }

    public static function middleware(): array
    {
        return [
            new Middleware(PermissionMiddleware::using(PermissionsEnum::BROWSE_STORES->value), only: ['index', 'datatable']),
            new Middleware(PermissionMiddleware::using(PermissionsEnum::ADD_STORES->value), only: ['create', 'store']),
            new Middleware(PermissionMiddleware::using(PermissionsEnum::EDIT_STORES->value), only: ['edit', 'update']),
            new Middleware(PermissionMiddleware::using(PermissionsEnum::DELETE_STORES->value), only: ['destroy']),
        ];
    }

    /**
     * Return list of resource consumed by datatables library
     */
    public function datatable(Request $request): JsonResponse
    {
        $query = $this->model::query();
        $user = Auth::user();
        $timezone = $user->timezone;

        if ($user->isOwner()) {
            $query->where('owner_id', $user->id);
        }

        return DataTables::eloquent($query)
            ->editColumn('category', function (Store $store) {
                return StoreCategory::from($store->category)->name;
            })
            ->editColumn('status', function (Store $store) {
                return StoreStatus::from($store->status)->name;
            })
            ->editColumn('owner_id', function (Store $store) {
                return $store->owner ? $store->owner->name : '';
            })
            ->editColumn('created_at', function (Store $store) use ($timezone) {
                return DateUtil::toUserLocalTime($store->created_at, $timezone);
            })
            ->editColumn('updated_at', function (Store $store) use ($timezone) {
                return DateUtil::toUserLocalTime($store->updated_at, $timezone);
            })
            ->toJson();
    }

    /**
     * Toggle merchant status
     */
    public function toggleStatus(Request $request, string $storeId): JsonResponse
    {
        $status = $request->get('status') === 'true' ? 1 : 0;

        $store = Store::findOrFail($storeId);

        Gate::authorize('update', $store);

        $store->update([
            'status' => StoreStatus::from($status)->value,
        ]);

        return response()->json([]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'operating_hours' => new OperatingHour
        ]);

        return parent::update($request, $id);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'operating_hours' => new OperatingHour
        ]);

        return parent::store($request);
    }
}
