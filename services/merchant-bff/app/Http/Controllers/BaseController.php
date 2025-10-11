<?php

namespace App\Http\Controllers;

use App\Core\AppFieldDef;
use App\Enums\InputType;
use App\Traits\HasFieldDef;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class BaseController extends Controller
{
    use HasFieldDef;

    protected Model $model;
    protected string $module;
    protected string $baseUrl;
    protected string $displayNameSingular;
    protected string $displayNamePlural;
    protected array $fieldDefs;

    /**
     * @param Model $model
     * @param String $module
     * @param string|null $baseUrl
     * @param string|null $displayNameSingular
     * @param string|null $displayNamePlural
     * @param AppFieldDef[] $fieldDefs
     */
    public function __construct(
        Model  $model,
        string $module,
        string $baseUrl = null,
        string $displayNameSingular = null,
        string $displayNamePlural = null,
        array  $fieldDefs = []
    )
    {
        $this->model = $model;
        $this->module = $module;
        $this->baseUrl = $baseUrl ?? url("manage/$module");
        $this->displayNameSingular = $displayNameSingular ?? $module;
        $this->displayNamePlural = $displayNamePlural ?? $module;
        $this->fieldDefs = $fieldDefs;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $module = str_replace('-', '_', $this->module);
        $view = "$module.browse";

        if (!view()->exists($view)) {
            $view = 'base.browse';
        }

        usort($this->fieldDefs, function ($first, $second) {
            return $first->columnOrder > $second->columnOrder;
        });


        return view($view, [
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
        $request->validate($this->getValidationRulesForAdding());

        try {
            DB::beginTransaction();

            $fieldsToCreate = $request->only($this->getCreatableColumn());

            foreach ($this->fieldDefs as $field) {
                if (!$field->creatable) {
                    continue;
                }

                switch ($field->inputType) {
                    case InputType::IMAGE:
                    case InputType::SELECT_MULTIPLE:
                        unset($fieldsToCreate[$field->column]);
                        break;
                    default:
                        break;
                }
            }

            $data = $this->model::create($fieldsToCreate);
            $id = $data->id;
            $fieldsToUpdate = [];

            foreach ($this->fieldDefs as $field) {
                if (!$field->creatable) {
                    continue;
                }

                if ($field->inputType === InputType::IMAGE) {
                    $column = $field->column;
                    $file = $request->file($column);
                    $extension = $file->extension();
                    $path = $file->storeAs($this->module, "{$column}_$id.$extension", 'public');
                    $fieldsToUpdate[$column] = $path;
                }

                if ($field->inputType === InputType::SELECT_MULTIPLE) {
                    $column = $field->column;
                    $data->{$column}()->sync($request->get($column));
                }
            }

            $data->update($fieldsToUpdate);

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
        $module = str_replace('-', '_', $this->module);
        $view = "$module.add_edit";

        if (!view()->exists($view)) {
            $view = 'base.add_edit';
        }

        return view($view, array_merge([
            'module' => $this->displayNameSingular,
            'baseUrl' => $this->baseUrl,
            'fieldDefs' => $this->fieldDefs,
        ], $dataToRender));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate($this->getValidationRulesForEditing());
        $data = $this->model::findOrFail($id);

        Gate::authorize('update', $data);

        try {
            DB::beginTransaction();

            $fieldsToUpdate = $request->only($this->getCreatableColumn());

            foreach ($this->fieldDefs as $field) {
                if (!$field->editable) {
                    continue;
                }

                if ($field->inputType === InputType::IMAGE && $request->file($field->column)) {
                    $column = $field->column;
                    $file = $request->file($column);
                    $extension = $file->extension();
                    $path = $file->storeAs($this->module, "{$column}_$id.$extension", 'public');
                    $fieldsToUpdate[$column] = $path;
                }

                if ($field->inputType === InputType::SELECT_MULTIPLE) {
                    $column = $field->column;
                    $data->{$column}()->sync($request->get($column));
                }
            }

            $data->update($fieldsToUpdate);

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }

        return response()->json(['status' => true, 'message' => "{$this->displayNameSingular} has been updated successfully."]);
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

        Gate::authorize('update', $data);

        $module = str_replace('-', '_', $this->module);
        $view = "$module.add_edit";

        if (!view()->exists($view)) {
            $view = 'base.add_edit';
        }

        return view($view, array_merge([
            'data' => $data,
            'module' => $this->displayNameSingular,
            'baseUrl' => $this->baseUrl,
            'fieldDefs' => $this->fieldDefs,
        ], $dataToRender));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        // TODO: Remove uploaded files/images if any
        $data = $this->model::findOrFail($id);

        Gate::authorize('delete', $data);

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
        $timezone = $user->timezone;

        return DataTables::of($this->model::query())
            ->make(true);
    }
}
