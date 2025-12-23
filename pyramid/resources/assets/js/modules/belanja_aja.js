const BelanjaAja = {
    baseUrl() {
        return $('.belanja-aja-datatable').data('url');
    },
    /**
     * @param options
     * @type options {tableAutoRefresh: bool, customActions: [], renderComplete: () {}, datatableOptions: {}}
     */
    index(options = {}) {
        BelanjaAja.loadTable(options);

        let table = $('.belanja-aja-datatable');

        table.on('click', '.action-edit', function (e) {
            e.preventDefault();
            let value = $(this).data('id');
            window.location.href = `${BelanjaAja.baseUrl()}/${value}/edit`;
        });

        table.on('click', '.action-whatsapp', function () {
            let id = $(this).data('id');
            let modal = Utils.loadModal('lg', true);
            let title = modal.find('.modal-title');
            let body = modal.find('.modal-body');

            title.text('Whatsapp Template');

            Utils.ajax(Utils.baseUrl('manage/shopping-orders/whatsapp-template'), 'GET', 'application/json', {id}, {
                success: function (response) {
                    body.html(response);
                }
            });
        });

        options?.renderComplete?.call(this);

        if (options.tableAutoRefresh) {
            // TODO: Find a better way to handle this (maybe websocket?)
            setInterval(() => {
                BelanjaAja.reloadTable();
            }, 15000);
        }
    },
    loadTable(options) {
        let statusCategory = options.statusCategory ?? '';
        let columns = $('.belanja-aja-datatable').find('thead th').map(function () {
            let row = $(this);
            let column = row.data('column');
            let name = row.data('name');
            let className = row.attr('class');
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
                        let remove = `
                            <button data-id="${row.id}" class="btn btn--icon action-delete" title="Delete" data-toggle="tooltip" data-placement="top"><i class="zmdi zmdi-delete"></i></button>
                        `;
                        return `<div class="table-actions">${actions ? actions : ''}${edit}${options.allowDelete ? remove : ''}</div>`;
                    }
                }
            }

            return {
                name,
                data: column,
                class: className,
            };
        });

        this.table = Utils.datatable('.belanja-aja-datatable', {
            ajax: {
                url: `${this.baseUrl()}/datatable?statusCategory=${statusCategory}`,
            },
            columns,
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
    },
    reloadTable() {
        this.table.ajax.reload();
    },
    process(orderId) {
        let modal = Utils.loadModal('lg', true);
        let title = modal.find('.modal-title');
        let body = modal.find('.modal-body');

        Utils.ajax(Utils.baseUrl(`manage/makan-aja/process/${orderId}`), 'GET', 'application/json', {}, {
            success: function (response) {
                title.text(response.title);
                body.html(response.body);
                $('.select2').select2({
                    dropdownAutoWidth: true,
                    width: "100%",
                });
            }
        });

        $(body).on('keydown', '[name="distance"]', function (e) {
            if (e.key === ',') {
                e.preventDefault();
                return false;
            }
        });

        $(body).on('blur', '[name="distance"]', function (e) {
            let subtotal = $(this).data('subtotal');
            let distance = $(this).val();
            let form = $(this).closest('form');

            Utils.disableSubmit(form);

            Utils.ajax(Utils.baseUrl('manage/makan-aja/calculate-fees'), 'POST', 'application/json', {
                subtotal, distance
            }, {
                success: function (response) {
                    form.find('[name="delivery_fee"]').val(response.delivery_fee);
                    form.find('[name="service_fee"]').val(response.service_fee);
                    form.find('[name="total"]').val(response.total);
                },
                complete: function () {
                    Utils.enableSubmit(form);
                }
            });
        });

        $(body).on('submit', 'form', function (e) {
            e.preventDefault();

            Swal({
                title: 'Apakah anda yakin?',
                text: 'Data ini tidak bisa diubah lagi setelah diproses!',
                type: 'warning',
                showCancelButton: true,
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    let form = $(this);
                    let url = form.attr('action');
                    let method = form.attr('method');
                    let data = {};

                    $.each(form.serializeArray(), function (_, kv) {
                        data[kv.name] = kv.value;
                    });

                    Utils.disableSubmit(form);

                    Utils.ajax(url, method, 'application/json', data, {
                        success: function (response) {
                            if (response.status) {
                                Utils.notify('', response.message);
                                modal.modal('hide');
                                BelanjaAja.reloadTable();
                            } else {
                                Utils.notify('', response.message);
                            }
                        },
                        error: function (response) {
                            Utils.notify('', response.responseJSON?.message);
                        },
                        complete: function () {
                            Utils.enableSubmit(form);
                        }
                    });
                },
                allowOutsideClick: false
            });
        });
    },
    details(orderId) {
        let modal = Utils.loadModal('full', true);
        let title = modal.find('.modal-title');
        let body = modal.find('.modal-body');

        title.text('Order Details');

        Utils.ajax(Utils.baseUrl(`manage/makan-aja/details/${orderId}`), 'GET', 'application/json', {}, {
            success: function (response) {
                body.html(response);
            }
        });
    },
    settings() {
        let modal = Utils.loadModal('md', true);
        let title = modal.find('.modal-title');
        let body = modal.find('.modal-body');

        title.text('Settings');

        Utils.ajax(Utils.baseUrl(`manage/makan-aja/settings`), 'GET', 'application/json', {}, {
            success: function (response) {
                body.html(response);
            }
        });

        $(body).on('submit', 'form', function (e) {
            e.preventDefault();

            let form = $(this);
            let url = form.attr('action');
            let method = form.attr('method');
            let data = {};

            $.each(form.serializeArray(), function (_, kv) {
                data[kv.name] = kv.value;
            });

            Utils.disableSubmit(form);

            Utils.ajax(url, method, 'application/json', data, {
                success: function (response) {
                    if (response.status) {
                        Utils.notify('', response.message);
                        modal.modal('hide');
                    } else {
                        Utils.notify('', response.message);
                    }
                },
                error: function (response) {
                    Utils.notify('', response.responseJSON?.message);
                },
                complete: function () {
                    Utils.enableSubmit(form);
                }
            });
        });
    },
}

export default BelanjaAja;
