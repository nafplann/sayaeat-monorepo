@use(App\Enums\RolesEnum)
@use(Illuminate\Support\Facades\Auth)

@extends('layouts.app')

@php
    $isEditing = isset($data);
@endphp

@section('title', $isEditing ? "Edit $module" : "Create $module")

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
                <div class="row">
                    <div class="col">
                        <div class="form-group required">
                            <label>Name</label>
                            <input type="text" class="form-control" placeholder="Enter user name" name="name"
                                   value="{{ $isEditing ? $data->name : '' }}">
                            <i class="form-group__bar"></i>
                        </div>
                        <div class="form-group required">
                            <label>Email</label>
                            <input type="email" class="form-control" placeholder="Enter user email" name="email"
                                   value="{{ $isEditing ? $data->email : '' }}">
                            <i class="form-group__bar"></i>
                        </div>
                        <div class="form-group required">
                            <label>Phone</label>
                            <input type="text" class="form-control" placeholder="eg. +628123456" name="phone_number"
                                   value="{{ $isEditing ? $data->phone_number : '' }}">
                            <i class="form-group__bar"></i>
                        </div>
                        <div class="form-group required">
                            <label>Password</label>
                            <input type="password" class="form-control"
                                   placeholder="{{ $isEditing ? 'Leave empty to keep the same password' : 'Enter password' }}"
                                   name="password">
                            <i class="form-group__bar"></i>
                        </div>
                        <div class="form-group required">
                            <label>Password Confirmation</label>
                            <input type="password" class="form-control" placeholder="Enter Password Again"
                                   name="password_confirmation">
                            <i class="form-group__bar"></i>
                        </div>
                        <div class="form-group required">
                            <label>Role</label>
                            <div class="select">
                                <select class="form-control select2" name="role">
                                    @foreach ($roles as $role)
                                        @if(! Auth::user()->isSuperAdmin() && $role->name === 'super admin')
                                            @continue
                                        @endif
                                        <option
                                            {{ $isEditing && $data->roles->contains($role) ? 'selected' : '' }} value="{{ $role->id }}">{{ ucfirst($role->name) }}</option>
                                    @endforeach
                                </select>
                                <i class="form-group__bar"></i>
                            </div>
                        </div>
                        <div class="form-group required">
                            <label>Timezone</label>
                            <div class="select">
                                <select class="form-control select2" name="timezone">
                                    <option
                                        {{ $isEditing && $data->timezone === 'Asia/Jayapura' ? 'selected' : '' }} value="Asia/Jayapura">
                                        Asia/Jayapura
                                    </option>
                                    <option
                                        {{ $isEditing && $data->timezone === 'Asia/Makassar' ? 'selected' : '' }} value="Asia/Makassar">
                                        Asia/Makassar
                                    </option>
                                    <option
                                        {{ $isEditing && $data->timezone === 'Asia/Jakarta' ? 'selected' : '' }} value="Asia/Jakarta">
                                        Asia/Jakarta
                                    </option>
                                </select>
                                <i class="form-group__bar"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-outline-primary btn--submit">Submit</button>
                <a href="{{ $baseUrl }}" class="btn btn-outline-dark">Go Back</a>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>Base.addEdit();</script>
@endsection
