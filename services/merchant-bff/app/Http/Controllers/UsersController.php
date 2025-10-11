<?php

namespace App\Http\Controllers;

use App\Core\AppFieldDef;
use App\Enums\InputType;
use App\Enums\PermissionsEnum;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Yajra\DataTables\Facades\DataTables;

class UsersController extends BaseController implements HasMiddleware
{
    public function __construct(User $model)
    {
        // TODO: Validate combination of country_code and phone_number
        $fieldDefs = [
            new AppFieldDef(column: 'id', label: 'ID', creatable: false, editable: false),
            new AppFieldDef(
                column: 'name',
                label: 'Name',
                validationRulesForAdding: ['required', 'min:3'],
                validationRulesForEditing: ['required', 'min:3'],
            ),
            new AppFieldDef(
                column: 'email',
                label: 'Email address',
                validationRulesForAdding: ['required', 'email', 'unique:users'],
            ),
            new AppFieldDef(
                column: 'phone_number',
                label: 'Phone number',
                validationRulesForAdding: ['required', 'starts_with:+62', 'regex:/^\S*$/u'],
                validationRulesForEditing: ['required', 'starts_with:+62', 'regex:/^\S*$/u'],
            ),
            new AppFieldDef(
                column: 'password',
                label: 'Password',
                browsable: false,
                validationRulesForAdding: ['required', 'min:6', 'confirmed'],
            ),
            new AppFieldDef(
                column: 'role',
                label: 'Role',
                browsable: false,
                validationRulesForAdding: ['required', 'exists:roles,id'],
                validationRulesForEditing: ['required', 'exists:roles,id'],
            ),
            new AppFieldDef(
                column: 'timezone',
                label: 'Timezone',
                inputType: InputType::SELECT,
                selectOptions: [
                    'default' => 'Asia/Jayapura',
                    'options' => [
                        'Asia/Makassar' => 'Asia/Makassar',
                        'Asia/Jakarta' => 'Asia/Jakarta',
                    ]
                ],
                browsable: false,
                validationRulesForAdding: ['required'],
                validationRulesForEditing: ['required'],
            ),
            new AppFieldDef(column: 'created_at', label: 'Created at', creatable: false, editable: false, datatableClass: 'text-center'),
            new AppFieldDef(column: 'updated_at', label: 'Updated at', creatable: false, editable: false, datatableClass: 'text-center'),
        ];

        parent::__construct(
            model: $model,
            module: 'users',
            displayNameSingular: 'User',
            displayNamePlural: 'Users',
            fieldDefs: $fieldDefs,
        );
    }

    public static function middleware(): array
    {
        return [
            new Middleware(PermissionMiddleware::using(PermissionsEnum::BROWSE_USERS->value), only: ['index', 'datatable']),
            new Middleware(PermissionMiddleware::using(PermissionsEnum::ADD_USERS->value), only: ['create', 'store']),
            new Middleware(PermissionMiddleware::using(PermissionsEnum::EDIT_USERS->value), only: ['edit', 'update']),
            new Middleware(PermissionMiddleware::using(PermissionsEnum::DELETE_USERS->value), only: ['destroy']),
        ];
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate($this->getValidationRulesForAdding());

        $data = $request->except('role');
        $role = Role::findById($request->get('role'));

        try {
            $selectedRole = $role->name;
            $user = $this->model::create($data);
            $user->syncRoles($selectedRole);

        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }

        return response()->json(['status' => true, 'message' => "{$this->displayNameSingular} has been added successfully."]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($data = []): View
    {
        return view("{$this->module}.add_edit", [
            'module' => $this->module,
            'baseUrl' => $this->baseUrl,
            'roles' => Role::all(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id, array $data = []): View
    {
        $data = $this->model::findOrFail($id);
        
        return view("{$this->module}.add_edit", [
            'data' => $data,
            'module' => $this->module,
            'baseUrl' => $this->baseUrl,
            'roles' => Role::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validationRules = $this->getValidationRulesForAdding();

        // Remove password from validation if it's not changed
        if (!$request->get('password')) {
            unset($validationRules['password']);
        }

        // Override email validation
        $validationRules['email'] = ['required', 'email', Rule::unique('users')->ignore($id)];

        // Retrieve the validated input...
        $validator = Validator::make($request->all(), $validationRules);
        $validator->validate();

        $user = $this->model::find($id);
        $data = $validator->safe()->except('role');
        $role = Role::findById($request->get('role'));

        if ($password = $request->get('password')) {
            $data['password'] = Hash::make($password);
        }

        try {
            $user->syncRoles($role->name);
            $user->update($data);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }

        return response()->json(['status' => true, 'message' => "{$this->displayNameSingular} has been updated successfully."]);
    }

    /**
     * Return list of resource consumed by datatables library
     */
    public function datatable(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = $this->model::query();

        if (!$user->isSuperAdmin()) {
            $query = User::role(['admin', 'owner']);
        }

        return DataTables::eloquent($query)
            ->editColumn('role', function (User $user) {
                return $user->roles->first()->name;
            })
            ->toJson();
    }
}
