@use(App\Enums\InputType)

@php
    $validationRules = $isEditing ? $field->validationRulesForEditing : $field->validationRulesForAdding;
    $isRequired = in_array('required', $validationRules) ? 'required' : '';
    $visible = $isEditing ? $field->editable : $field->creatable;
@endphp

@if($visible)
    @if ($field->inputType === InputType::HIDDEN)
        <input type="hidden" class="form-control" placeholder="{{ $field->placeholder }}" name="{{ $field->column }}" value="{{ $isEditing ? $data->{$field->column} : '' }}">
    @else
        <div class="form-group {{ $isRequired }}">
            <label>{{ $field->label }}</label>
            <input type="text" class="form-control input-mask" data-mask="#.##0" data-reverse="true" placeholder="{{ $field->placeholder }}" name="{{ $field->column }}" value="{{ $isEditing ? $data->{$field->column} : '' }}">
            <i class="form-group__bar"></i>
        </div>
    @endif
@endif
