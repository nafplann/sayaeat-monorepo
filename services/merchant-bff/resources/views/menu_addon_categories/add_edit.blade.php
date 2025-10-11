@extends('layouts.app')

@use(App\Enums\InputType)

@php
    $isEditing = isset($data);
@endphp

@section('title', $isEditing ? "Edit $module" : "Create $module")

@section('content')
    <form
        class="base-form"
        action="{{ $isEditing ? "$baseUrl/{$data->id}" : $baseUrl }}"
        method="POST"
        enctype="multipart/form-data"
        data-is-editing={{ $isEditing }}>
        <div class="card">
            <div class="card-body">
                {{ $isEditing ? method_field('PUT') : '' }}
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

                                @case(InputType::SELECT)
                                    @include('base.input_type.select')
                                    @break

                                @case(InputType::FILE)
                                    @include('base.input_type.file')
                                    @break

                                @case(InputType::NUMBER)
                                    @include('base.input_type.number')
                                    @break

                                @case(InputType::DECIMAL)
                                    @include('base.input_type.decimal')
                                    @break
                            @endswitch
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Addons</h4>
                <div class="actions">
                    <a href="" class="actions__item zmdi zmdi-plus" id="add-addon"></a>
                </div>
                <div id="addon-list">
                    @if ($isEditing)
                        @foreach($data->addons as $addon)
                            <div class="row" data-id="{{ $addon->id }}">
                                <div class="col">
                                    <div class="form-group">
                                        <input type="text" class="form-control" placeholder="Name" name="addon_name[{{ $addon->id }}]" value="{{ $addon->name }}">
                                        <i class="form-group__bar"></i>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-group">
                                        <input type="text" class="form-control" placeholder="Price" name="addon_price[{{ $addon->id }}]" value="{{ $addon->price }}">
                                        <i class="form-group__bar"></i>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-block btn-danger remove-addon">Delete</button>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Link to Menus</h4>
                <div class="form-group">
                    <label>Menus</label>
                    <select class="select form-control" name="menu_to_link[]" multiple>
                        <option disabled>Select menu to link</option>
                        @if ($isEditing)
                            @foreach ($data->merchant->menus as $menu)
                                <option {{ $data->menus->contains($menu) ? 'selected' : '' }} value="{{ $menu->id }}">{{ $menu->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>
        </div>

        <div class="row mb-5">
            <div class="col-md-12">
                <button type="submit" class="btn btn-outline-primary btn--submit">Submit</button>
                <a href="{{ $baseUrl }}" class="btn btn-outline-dark btn--back">Go Back</a>
            </div>
        </div>
    </form>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            Base.addEdit();

            const isEditing = $('.base-form').data('is-editing') === 1;
            const addonList = $('#addon-list');
            const merchant = $('[name="merchant_id"]');
            const menuToLink = $('[name="menu_to_link[]"]');

            $(document).on('click', '#add-addon', function (e) {
                e.preventDefault();

                addonList.append(`
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <input type="text" class="form-control" placeholder="Name" name="addon_name[]">
                                <i class="form-group__bar"></i>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <input type="text" class="form-control" placeholder="Price" name="addon_price[]">
                                <i class="form-group__bar"></i>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-block btn-danger remove-addon">Delete</button>
                        </div>
                    </div>
                `);
            });

            $(document).on('click', '.remove-addon', function (e) {
                e.preventDefault();

                if (isEditing) {
                    const id = $(this).closest('.row').data('id');
                    Utils.ajax(Utils.baseUrl(`manage/menu-addon-categories/addon-delete/${id}`), 'DELETE');
                }

                $(this).closest('.row').remove();
            });

            merchant.change(function () {
                const selectedMerchantId = $(this).val();
                Utils.ajax(Utils.baseUrl(`manage/menus/by-merchant/${selectedMerchantId}`), 'GET')
                    .then((results) => {
                        menuToLink.find('option').remove();
                        results.forEach((item) => {
                           menuToLink.append(`<option value="${item.id}">${item.name}</option>`);
                        });
                    });
            });
        });
    </script>
@endsection
