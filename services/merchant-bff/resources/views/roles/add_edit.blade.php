@extends('layouts.app')

@php
    $isEditing = isset($role);
@endphp

@section('title', $isEditing ? 'Edit Role' : 'Create Role')

@section('content')
    <div class="card">
        <div class="card-body">
            <form
                class="base-form"
                action="{{ $isEditing ? url("/manage/roles/{$role->id}") : url('/manage/roles') }}"
                method="POST"
                enctype="multipart/form-data">
                {{ $isEditing ? method_field('PUT') : '' }}
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group required">
                            <label>Name</label>
                            <input type="text" class="form-control" placeholder="Enter Role Name" name="name" value="{{ $isEditing ? $role->name : '' }}">
                            <i class="form-group__bar"></i>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group required mb-4">
                            <label>Permissions</label>
                        </div>
                    </div>
                    @foreach($permissions as $permission)
                        <div class="col-md-2">
                            <div class="form-group permission-item">
                                <div class="toggle-switch">
                                    <input type="checkbox"
                                       id="{{ $permission->id }}"
                                       class="toggle-switch__checkbox"
                                       name="permissions[]"
                                       value="{{ $permission->id }}"
                                        {{ $isEditing && $role->hasPermissionTo($permission) ? 'checked' : '' }} />
                                    <i class="toggle-switch__helper"></i>
                                </div>
                                <label for="{{ $permission->id }}">{{ ucwords($permission->name) }}</label>
                            </div>
                        </div>
                    @endforeach
                </div>
                <button type="submit" class="btn btn-outline-primary btn--submit">Submit</button>
                <a href="{{ url('manage/roles') }}" class="btn btn-outline-dark">Go Back</a>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>Base.addEdit();</script>
@endsection
