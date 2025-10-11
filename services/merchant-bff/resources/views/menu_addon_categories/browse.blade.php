@extends('layouts.app')

@section('title', ucwords($module))

@section('content')
    <div class="toolbar">
        <select class="select2" multiple data-placeholder="Select one or more merchants" name="merchant_filter" id="merchant-filter">
            @php
                $user = auth()->user();
                $merchants = $user->isOwner() ? $user->merchants : \App\Models\Merchant::all();
            @endphp

            @foreach($merchants as $merchant)
                <option value="{{ $merchant->id }}">{{ $merchant->name }}</option>
            @endforeach
        </select>
    </div>

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
                            <th data-column="{{ $field->column  }}" class="{{ $field->datatableClass ?? '' }}">{{ $field->label }}</th>
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
    <script>
        const merchantFilter = $('#merchant-filter');

        Base.index({
            datatableOptions: {
                ajax: {
                    url: `${Base.baseUrl()}/datatable`,
                    data: (existing) => ({
                        ...existing,
                        merchant_ids: merchantFilter.val()
                    })
                },
            }
        });

        $(document).ready(function() {
            $('.select2').select2({ width: '100%'})

            merchantFilter.change(function () {
                Base.reloadTable();
            })
        });
    </script>
@endsection
