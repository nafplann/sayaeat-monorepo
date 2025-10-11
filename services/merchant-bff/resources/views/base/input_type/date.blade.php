@use(App\Enums\InputType)

@php
    $validationRules = $isEditing ? $field->validationRulesForEditing : $field->validationRulesForAdding;
    $isRequired = in_array('required', $validationRules) ? 'required' : '';
    $visible = $isEditing ? $field->editable : $field->creatable;
@endphp

@if($visible)
    <div class="form-group {{ $isRequired }}">
        <label>{{ $field->label }}</label>
        <input type="date" class="form-control date-picker" placeholder="{{ $field->placeholder }}" name="{{ $field->column }}" value="{{ $isEditing ? $data->{$field->column} : '' }}">
    </div>
@endif
