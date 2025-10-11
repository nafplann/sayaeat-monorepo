@use(App\Enums\InputType)

@php
    $validationRules = $isEditing ? $field->validationRulesForEditing : $field->validationRulesForAdding;
    $isRequired = in_array('required', $validationRules) ? 'required' : '';
    $visible = $isEditing ? $field->editable : $field->creatable;
@endphp

@if($visible)
    @php
        $select = (object) $field->selectOptions;
        $default = $isEditing ? $data->{$field->column} : ($select->default ?? '');
    @endphp
    <div class="form-group {{ $isRequired }}">
        <label>{{ $field->label }}</label>
        <select class="select form-control" name="{{ $field->column }}[]" multiple>
            @foreach($select->options as $label => $value)
                <option {{ $value === $default ? 'selected' : '' }} value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
    </div>
@endif
