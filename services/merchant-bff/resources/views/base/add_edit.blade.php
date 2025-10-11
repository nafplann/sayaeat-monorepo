@extends('layouts.app')

@php
    $isEditing = isset($data);
@endphp

@section('title', $isEditing ? __('app.edit_module', ['module' => $module]) : __('app.create_module', ['module' => $module]))

@section('content')
    <div class="card">
        <div class="card-body">
            <form
                class="base-form"
                action="{{ $isEditing ? "$baseUrl/{$data->id}" : $baseUrl }}"
                method="POST"
                enctype="multipart/form-data"
                data-is-editing={{ $isEditing }}>
                {{ $isEditing ? method_field('PUT') : '' }}
                @include('base.fields')
                <button type="submit"
                        class="btn btn-outline-primary btn--submit">{{ $isEditing ? __('app.update') : __('app.submit') }}</button>
                <a href="{{ $baseUrl }}" class="btn btn-outline-dark btn--back">{{ __('app.go_back') }}</a>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="text/javascript"
            src="https://cdn.jsdelivr.net/npm/browser-image-compression@2.0.2/dist/browser-image-compression.js"></script>
    <script>Base.addEdit();</script>
@endsection
