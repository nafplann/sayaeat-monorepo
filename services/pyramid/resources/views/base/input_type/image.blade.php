@use(App\Enums\InputType)

@php
    $validationRules = $isEditing ? $field->validationRulesForEditing : $field->validationRulesForAdding;
    $isRequired = in_array('required', $validationRules) ? 'required' : '';
    $visible = $isEditing ? $field->editable : $field->creatable;
@endphp

@if($visible)
    <div class="form-group {{ $isRequired }}">
        <label>{{ $field->label }}</label>
        <div class="app-image_picker" data-name="{{ $field->column }}" data-placeholder-image="{{ $isEditing ? $data->{$field->column} : '' }}"></div>
    </div>
@endif
