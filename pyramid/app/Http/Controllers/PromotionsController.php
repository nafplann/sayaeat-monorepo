<?php

namespace App\Http\Controllers;

use App\Core\AppFieldDef;
use App\Enums\InputType;
use App\Enums\PermissionsEnum;
use App\Models\Promotion;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rules\File;
use Spatie\Permission\Middleware\PermissionMiddleware;

class PromotionsController extends BaseController implements HasMiddleware
{
    public function __construct(Promotion $model)
    {
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
                label: __('app.name'),
            ),
            new AppFieldDef(
                column: 'banner_path',
                label: 'Banner',
                inputType: InputType::IMAGE,
                browsable: false,
                validationRulesForAdding: ['required', File::types(['png', 'jpg', 'jpeg'])->max('1mb')],
                validationRulesForEditing: [File::types(['png', 'jpg', 'jpeg'])->max('1mb')],
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
            new AppFieldDef(column: 'created_at', label: 'Created at', creatable: false, editable: false, datatableClass: 'text-center'),
            new AppFieldDef(column: 'updated_at', label: 'Updated at', creatable: false, editable: false, datatableClass: 'text-center'),
        ];

        parent::__construct(
            model: $model,
            module: 'promotions',
            displayNameSingular: 'Promotion',
            displayNamePlural: 'Promotions',
            fieldDefs: $fieldDefs,
        );
    }

    public static function middleware(): array
    {
        return [
            new Middleware(PermissionMiddleware::using(PermissionsEnum::BROWSE_PROMOTIONS->value), only: ['index', 'datatable']),
            new Middleware(PermissionMiddleware::using(PermissionsEnum::ADD_PROMOTIONS->value), only: ['create', 'store']),
            new Middleware(PermissionMiddleware::using(PermissionsEnum::EDIT_PROMOTIONS->value), only: ['edit', 'update']),
            new Middleware(PermissionMiddleware::using(PermissionsEnum::DELETE_PROMOTIONS->value), only: ['destroy']),
        ];
    }
}
