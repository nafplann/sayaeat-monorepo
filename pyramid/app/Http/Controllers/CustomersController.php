<?php

namespace App\Http\Controllers;

use App\Core\AppFieldDef;
use App\Core\Http\ApiResponse;
use App\Enums\InputType;
use App\Enums\PermissionsEnum;
use App\Models\Archive;
use App\Models\ArchiveFile;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\View\View;
use Spatie\Permission\Middleware\PermissionMiddleware;

class CustomersController extends BaseController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(PermissionMiddleware::using(PermissionsEnum::BROWSE_CUSTOMERS->value), only: ['index', 'datatable']),
            new Middleware(PermissionMiddleware::using(PermissionsEnum::ADD_CUSTOMERS->value), only: ['create', 'store']),
            new Middleware(PermissionMiddleware::using(PermissionsEnum::EDIT_CUSTOMERS->value), only: ['edit', 'update']),
            new Middleware(PermissionMiddleware::using(PermissionsEnum::DELETE_CUSTOMERS->value), only: ['destroy']),
        ];
    }

    public function __construct(Customer $model)
    {
        $fieldDefs = [
            new AppFieldDef(column: 'id', label: 'ID', browsable: false, creatable: false, editable: false),
            new AppFieldDef(
                column: 'name',
                label: 'Name',
                validationRulesForAdding: ['required', 'min:3'],
                validationRulesForEditing: ['required', 'min:3'],
            ),
            new AppFieldDef(
                column: 'phone_number',
                label: 'Phone Number',
                validationRulesForAdding: ['required'],
                validationRulesForEditing: ['required'],
            ),
            new AppFieldDef(
                column: 'whatsapp_number',
                label: 'Whatsapp',
            ),
            new AppFieldDef(
                column: 'address',
                label: 'Address',
                browsable: false,
                validationRulesForAdding: ['required'],
                validationRulesForEditing: ['required'],
            ),
            new AppFieldDef(
                column: 'occupation',
                label: 'Occupation',
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
                column: 'map_link',
                label: 'Map Link',
                browsable: false,
            ),
            new AppFieldDef(
                column: 'facebook_link',
                label: 'Facebook Link',
                browsable: false,
            ),
            new AppFieldDef(
                column: 'instagram_link',
                label: 'Instagram Link',
                browsable: false,
            ),
            new AppFieldDef(column: 'created_at', label: 'Created at', creatable: false, editable: false, datatableClass: 'text-center'),
            new AppFieldDef(column: 'updated_at', label: 'Updated at', creatable: false, editable: false, datatableClass: 'text-center'),
        ];

        parent::__construct(
            model: $model,
            module: 'customers',
            displayNameSingular: 'Customer',
            displayNamePlural: 'Costumers',
            fieldDefs: $fieldDefs,
        );
    }
}
