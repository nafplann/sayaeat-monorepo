@extends('layouts.app')

@php
    $isEditing = isset($data);
@endphp

@section('title', $isEditing ? __('app.edit_module', ['module' => $module]) : __('app.create_module', ['module' => $module]))

@section('content')
    <form
        class="base-form"
        action="{{ $isEditing ? "$baseUrl/{$data->id}" : $baseUrl }}"
        method="POST"
        enctype="multipart/form-data"
        data-is-editing={{ $isEditing }}>
        {{ $isEditing ? method_field('PUT') : '' }}
            <div class="card">
                <div class="card-body">

                @include('base.fields')
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h4 class="card-title">{{ __('app.link_to_product') }}</h4>
                <div class="form-group">
                    <label>{{ __('app.products') }}</label>
                    <select class="select form-control" name="product_to_link[]" multiple>
                        <option disabled>{{ __('app.select_product') }}</option>
                        @if ($isEditing)
                            @foreach ($data->store->products as $product)
                                <option {{ $data->products->contains($product) ? 'selected' : '' }} value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>
        </div>

        <div class="row mb-5">
            <div class="col-md-12">
                <button type="submit"
                        class="btn btn-outline-primary btn--submit">{{ $isEditing ? __('app.update') : __('app.submit') }}</button>
                <a href="{{ $baseUrl }}" class="btn btn-outline-dark btn--back">{{ __('app.go_back') }}</a>
            </div>
        </div>
    </form>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            Base.addEdit();

            const store = $('[name="store_id"]');
            const productToLink = $('[name="product_to_link[]"]');

            store.change(function () {
                const selectedStoreId = $(this).val();
                Utils.ajax(Utils.baseUrl(`manage/products/by-store/${selectedStoreId}`), 'GET')
                    .then((results) => {
                        productToLink.find('option').remove();
                        results.forEach((item) => {
                            productToLink.append(`<option value="${item.id}">${item.name}</option>`);
                        });
                    });
            });
        });
    </script>
@endsection
