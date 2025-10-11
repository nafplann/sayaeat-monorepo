@extends('layouts.app')

@section('title', ucwords($module))

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="table-responsive table-hover">
                <table class="table table-bordered base-datatable" data-url="{{ $baseUrl }}">
                    <thead>
                    <tr>
                        <th data-column="id">ID</th>
                        <th data-column="user_id">User</th>
                        <th data-column="event">Event</th>
                        <th data-column="auditable_type">Auditable Type</th>
                        <th data-column="auditable_id">Auditable ID</th>
                        <th data-column="old_values">Old Values</th>
                        <th data-column="new_values">New Values</th>
                        <th data-column="created_at" class="text-center">Created At</th>
                        <th data-column="updated_at" class="text-center">Updated At</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        .audit-values {
            max-width: 360px;
        }
    </style>
@endsection

@section('scripts')
    <script>
        Base.index();
    </script>
@endsection
