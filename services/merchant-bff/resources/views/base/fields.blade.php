@use(App\Enums\InputType)

<div class="row">
    <div class="col">
        @foreach($fieldDefs as $field)
            @switch($field->inputType)
                @case(InputType::TEXT)
                @case(InputType::HIDDEN)
                    @include('base.input_type.text')
                    @break

                @case(InputType::DATE)
                    @include('base.input_type.date')
                    @break

                @case(InputType::DATETIME)
                    @include('base.input_type.datetime')
                    @break

                @case(InputType::SELECT)
                    @include('base.input_type.select')
                    @break

                @case(InputType::SELECT_MULTIPLE)
                    @include('base.input_type.select_multiple')
                    @break

                @case(InputType::FILE)
                    @include('base.input_type.file')
                    @break

                @case(InputType::NUMERIC)
                    @include('base.input_type.numeric')
                    @break

                @case(InputType::NUMBER)
                    @include('base.input_type.number')
                    @break

                @case(InputType::DECIMAL)
                    @include('base.input_type.decimal')
                    @break

                @case(InputType::IMAGE)
                    @include('base.input_type.image')
                    @break
            @endswitch
        @endforeach
    </div>
</div>
