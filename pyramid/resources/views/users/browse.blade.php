@extends('layouts.app')

@section('title', ucwords($module))

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered base-datatable" data-url="{{ $baseUrl }}">
                    <thead>
                    <tr>
                        <th data-column="id" data-name="id">ID</th>
                        <th data-column="email" data-name="email">Email</th>
                        <th data-column="name" data-name="name">Name</th>
                        <th data-column="phone_number" data-name="phone_number">Phone Number</th>
                        <th data-column="role" data-name="role" data-searchable="false">Role</th>
                        <th data-column="created_at" data-name="created_at" class="text-center">Created At</th>
                        <th data-column="updated_at" data-name="updated_at" class="text-center">Updated At</th>
                        <th data-column="_action" data-name="_action" class="text-center">Action</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>Base.index();</script>
@endsection
