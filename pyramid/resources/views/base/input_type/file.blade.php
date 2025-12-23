@use(App\Enums\InputType)

@php
    $validationRules = $isEditing ? $field->validationRulesForEditing : $field->validationRulesForAdding;
    $isRequired = in_array('required', $validationRules) ? 'required' : '';
    $visible = $isEditing ? $field->editable : $field->creatable;
@endphp

@if($visible)
    <div class="form-group {{ $isRequired }}">
        <label>{{ $field->label }}</label>
        <div class="dropzone dropzone-upload lightbox"></div>
    </div>
@endif
