const MakanAja = {
    baseUrl() {
        return $('.makan-aja-datatable').data('url');
    },
    /**
     * @param options
     * @type options {tableAutoRefresh: bool, customActions: [], renderComplete: () {}, datatableOptions: {}}
     */
    index(options = {}) {
        MakanAja.loadTable(options);

        let table = $('.makan-aja-datatable');

        table.on('click', '.action-process', function (e) {
            e.preventDefault();
            let value = $(this).data('id');
            MakanAja.process(value);
        });

        table.on('click', '.action-details', function (e) {
            e.preventDefault();
            let value = $(this).data('id');
            MakanAja.details(value);
        });

        table.on('click', '.action-cancel', function (e) {
            e.preventDefault();
            let value = $(this).data('id');
            MakanAja.cancel(value);
        });

        $('#makan-settings-button').on('click', function (e) {
            MakanAja.settings();
        });

        options?.renderComplete?.call(this);

        if (options.tableAutoRefresh) {
            // TODO: Find a better way to handle this (maybe websocket?)
            setInterval(() => {
                MakanAja.reloadTable();
            }, 15000);
        }
    },
    loadTable(options) {
        let statusCategory = options.statusCategory ?? '';
        let columns = $('.makan-aja-datatable').find('thead th').map(function () {
            let row = $(this);
            let column = row.data('column');
            let columnOrder = row.data('column-order') ?? 1;
            let className = row.attr('class');

            if (column === 'duration') {
                return {
                    name: column,
                    data: column,
                    orderable: false,
                    searchable: false,
                    class: '',
                    order: columnOrder,
                    render: function (data, type, row, meta) {
                        // Dont show duration if status cancelled or completed
                        if (row.status === 0 || row.status === 9) {
                            return '-';
                        }
                        return moment(row.created_at, 'DD-MM-YYYY HH:mm:ss').fromNow();
                    }
                }
            }

            if (column === '_action') {
                return {
                    name: column,
                    data: column,
                    orderable: false,
                    searchable: false,
                    class: '',
                    order: columnOrder,
                    render: function (data, type, row, meta) {
                        let process = `
                            <button data-id="${row.id}" class="btn btn--icon action-process" title="Process" data-toggle="tooltip" data-placement="top"><i class="zmdi zmdi-check-circle"></i></button>
                        `;
                        let detail = `
                            <button data-id="${row.id}" class="btn btn--icon action-details" title="Order Details" data-toggle="tooltip" data-placement="top"><i class="zmdi zmdi-eye"></i></button>
                        `;
                        let cancel = `
                            <button data-id="${row.id}" class="btn btn--icon action-cancel" title="Cancel Order" data-toggle="tooltip" data-placement="top"><i class="zmdi zmdi-delete"></i></button>
                        `;

                        let actionableStatusCode = [2, 4, 6, 7, 8];

                        if (!actionableStatusCode.includes(row.status)) {
                            process = '';
                        }

                        // Dont show cancel button if status is completed
                        if (row.status === 0 || row.status === 9) {
                            cancel = '';
                        }

                        return `<div class="table-actions">${process}${detail}${cancel}</div>`;
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

        let datatableButtons = `
            <div class="dataTables_buttons actions">
                <span class="actions__item zmdi zmdi-refresh" data-table-action="reload" title="" data-toggle="tooltip" data-placement="top" data-original-title="Reload"></span>
            </div>
        `
        this.table = Utils.datatable('.makan-aja-datatable', {
            ajax: {
                url: `${this.baseUrl()}/datatable?statusCategory=${statusCategory}`,
            },
            columns,
            order: [[15, 'desc']],
            lengthMenu: [[15, 30, 50, 100], ['15 Rows', '30 Rows', '50 Rows', '100 Rows']],
            createdRow: function (row, data, dataIndex) {
                let actionableStatus = [4, 6, 7, 8];

                if (actionableStatus.includes(data.status)) {
                    $(row).addClass('table-success');
                }

                if (data.status === 2) {
                    $(row).addClass('table-warning');
                }
            },
            ...(options?.datatableOptions ?? {})
        }, datatableButtons);

        // Data table button actions
        $('body').on('click', '[data-table-action]', function (e) {
            e.preventDefault();

            let action = $(this).data('table-action');

            if (action === 'reload') {
                MakanAja.reloadTable();
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
                                MakanAja.reloadTable();
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
    cancel(orderId) {
        Swal({
            title: 'Apakah anda yakin membatalkan pesanan ini?',
            text: 'Pesanan ini tidak bisa diubah lagi!',
            type: 'warning',
            input: "text",
            inputAttributes: {
                placeholder: "Catatan: misalnya, stok habis, dll.",
            },
            inputValidator: (value) => {
                if (!value) {
                    return 'Catatan pembatalan tidak boleh kosong!';
                }
            },
            confirmButtonColor: "#ff6b68",
            showCancelButton: true,
            showLoaderOnConfirm: true,
            confirmButtonText: "Ya, Batalkan!",
            preConfirm: (reason) => {
                Utils.ajax(Utils.baseUrl(`manage/makan-aja/cancel/${orderId}?reason=${reason}`), 'POST', 'application/json', {}, {
                    success: function (response) {
                        if (response.status) {
                            Utils.notify('', response.message);
                            MakanAja.reloadTable();
                        } else {
                            Utils.notify('', response.message);
                        }
                    },
                    error: function (response) {
                        Utils.notify('', response.responseJSON?.message);
                    }
                });
            },
            allowOutsideClick: false
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

export default MakanAja;
