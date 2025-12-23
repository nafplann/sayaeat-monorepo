@extends('layouts.app')

@section('title', ucwords($module))

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered base-datatable" data-url="{{ $baseUrl }}">
                    <thead>
                        <tr>
                            @foreach($fieldDefs as $field)
                                @if(!$field->browsable)
                                    @continue
                                @endif
                                <th data-column="{{ $field->column  }}" data-column-order={{ $field->columnOrder }} class="{{ $field->datatableClass ?? '' }}">{{ $field->label }}</th>
                            @endforeach
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
