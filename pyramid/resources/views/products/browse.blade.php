@extends('layouts.app')

@section('title', ucwords($module))

@section('content')
    <div class="toolbar">
        <select class="select2" multiple data-placeholder="Select one or more stores" name="store_filter" id="store-filter">
            @php
                $user = auth()->user();
                $stores = $user->isOwner() ? $user->stores : \App\Models\Store::all();
            @endphp

            @foreach($stores as $store)
                <option value="{{ $store->id }}">{{ $store->name }}</option>
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
                            @if($field->column === 'status')
                                <th data-column="{{ $field->column  }}" data-column-order={{ $field->columnOrder }} class="{{ $field->datatableClass ?? '' }}">
                                    <div class="align-content-center justify-content-center hide" id="all-menu-toggle" hidden style="display: flex;">
                                        <div class="toggle-switch">
                                            <input type="checkbox" class="toggle-switch__checkbox">
                                            <i class="toggle-switch__helper"></i>
                                        </div>
                                    </div>
                                </th>
                            @else
                            <th data-column="{{ $field->column  }}" data-column-order={{ $field->columnOrder }} class="{{ $field->datatableClass ?? '' }}">{{ $field->label }}</th>
                            @endif
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
        const storeFilter = $('#store-filter');
        const allMenuToggle = $('#all-menu-toggle');

        Products.index({
            datatableOptions: {
                ajax: {
                    url: `${Base.baseUrl()}/datatable`,
                    data: (existing) => ({
                        ...existing,
                        store_ids: storeFilter.val()
                    })
                },
            }
        });

        $(document).ready(function() {
            $('.select2').select2({ width: '100%'})

            storeFilter.change(function () {
                Products.reloadTable();

                let value = $(this).val();
                allMenuToggle.attr('hidden', !value.length);
            });
        });

        allMenuToggle.on('change', 'input[type="checkbox"]', function() {
            let checked = $(this).is(':checked');
            let stores = storeFilter.val();

            $('.menu-status-switch').attr('checked', checked);

            Utils.ajax(`${Products.baseUrl()}/toggle-all-status/${stores.join(',')}?status=${checked}`)
                .then(() => Utils.notify('', 'Update menu berhasil'))
                .catch(() => Utils.notify('', 'Gagal memperbarui produk'));
        });

    </script>
@endsection
