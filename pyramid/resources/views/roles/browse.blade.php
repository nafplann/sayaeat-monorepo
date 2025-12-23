@extends('layouts.app')

@section('title', 'Roles')

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered base-datatable" data-url="{{ url('/manage/roles') }}">
                    <thead>
                        <tr>
                            <th data-column="id" data-name="id">ID</th>
                            <th data-column="name" data-name="name">Name</th>
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
