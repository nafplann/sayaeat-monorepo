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
        const merchantFilter = $('#merchant-filter');
        const allMenuToggle = $('#all-menu-toggle');

        Menus.index({
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
                Menus.reloadTable();

                let value = $(this).val();
                allMenuToggle.attr('hidden', !value.length);
            });
        });

        allMenuToggle.on('change', 'input[type="checkbox"]', function() {
            let checked = $(this).is(':checked');
            let merchants = merchantFilter.val();

            $('.menu-status-switch').attr('checked', checked);

            Utils.ajax(`${Menus.baseUrl()}/toggle-all-status/${merchants.join(',')}?status=${checked}`)
                .then(() => Utils.notify('', 'Update menu berhasil'))
                .catch(() => Utils.notify('', 'Gagal memperbarui menu'));
        });

    </script>
@endsection
