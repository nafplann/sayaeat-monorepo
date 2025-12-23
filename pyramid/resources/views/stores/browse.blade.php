@extends('base.browse')

@section('scripts')
    <script>
        Base.loadTable = function (options) {
            let columns = $('.base-datatable').find('thead th').map(function () {
                let row = $(this);
                let column = row.data('column');
                let columnOrder = row.data('column-order') ?? 1;
                let className = row.attr('class');

                if (column === 'status') {
                    return {
                        name: column,
                        data: column,
                        orderable: false,
                        searchable: false,
                        class: '',
                        order: columnOrder,
                        render: function (data, type, row, meta) {
                            return `
                                <div class="d-flex align-content-center justify-content-center">
                                    <div class="toggle-switch">
                                        <input type="checkbox" class="toggle-switch__checkbox store-status-switch" value="${row.id}" ${row.status === 'ACTIVE' ? 'checked' : ''}>
                                        <i class="toggle-switch__helper"></i>
                                    </div>
                                </div>
                            `;
                        }
                    }
                }

                if (column === '_action') {
                    return {
                        data: null,
                        sortable: false,
                        searchable: false,
                        class: 'text-center',
                        render: function (data, type, row, meta) {
                            let actions = options?.customActions?.reduce((prev, item) => {
                                return prev + `<button data-id="${row.id}" class="btn btn--icon ${item.className}" title="${item.title}" data-toggle="tooltip" data-placement="top"><i class="${item.icon}"></i></button>`
                            }, '');
                            let edit = `
                        <button data-id="${row.id}" class="btn btn--icon action-edit" title="Edit" data-toggle="tooltip" data-placement="top"><i class="zmdi zmdi-edit"></i></button>
                    `;
                            return `<div class="table-actions">${edit}</div>`;
                        }
                    }
                }

                return {
                    name: column,
                    data: column,
                    class: className,
                    order: columnOrder,
                };
            });

            this.table = Utils.datatable('.base-datatable', {
                ajax: {
                    url: `${this.baseUrl()}/datatable`,
                },
                columns,
                order: [[7, 'desc']],
                ...(options?.datatableOptions ?? {})
            });

            // Data table button actions
            $('body').on('click', '[data-table-action]', function (e) {
                e.preventDefault();

                let action = $(this).data('table-action');

                if (action === 'create') {
                    window.location.href = `${Base.baseUrl()}/create`;
                }
                if (action === 'reload') {
                    Base.reloadTable();
                }
                if (action === 'excel') {
                    $(this).closest('.dataTables_wrapper').find('.buttons-excel').trigger('click');
                }
                if (action === 'csv') {
                    $(this).closest('.dataTables_wrapper').find('.buttons-csv').trigger('click');
                }
                if (action === 'print') {
                    $(this).closest('.dataTables_wrapper').find('.buttons-print').trigger('click');
                }
                if (action === 'fullscreen') {
                    var parentCard = $(this).closest('.card');

                    if (parentCard.hasClass('card--fullscreen')) {
                        parentCard.removeClass('card--fullscreen');
                        $('body').removeClass('data-table-toggled');
                    } else {
                        parentCard.addClass('card--fullscreen')
                        $('body').addClass('data-table-toggled');
                    }
                }
            });
        };

        Base.index();

        $('.base-datatable').on('change', '.store-status-switch', function (e) {
            e.preventDefault();
            let value = $(this).val();
            let checked = $(this).is(':checked');

            Utils.ajax(`${Base.baseUrl()}/toggle-status/${value}?status=${checked}`)
                .then(() => Utils.notify('', 'Update status toko berhasil'))
                .catch(() => Utils.notify('', 'Gagal memperbarui status toko'));
        })
    </script>
@endsection
