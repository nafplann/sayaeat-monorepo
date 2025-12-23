const Menus = {
    baseUrl() {
        return $('.base-datatable').data('url');
    },
    /**
     * @param options
     * @type options {customActions: [], renderComplete: () {}, datatableOptions: {}}
     */
    index(options = {}) {

        Menus.loadTable(options);

        let table = $('.base-datatable');

        table.on('click', '.action-edit', function (e) {
            e.preventDefault();
            let value = $(this).data('id');
            window.location.href = `${Menus.baseUrl()}/${value}/edit`;
        });

        table.on('click', '.action-delete', function (e) {
            e.preventDefault();
            let value = $(this).data('id');
            Menus.delete(value);
        });

        table.on('change', '.menu-status-switch', function (e) {
            e.preventDefault();
            let value = $(this).val();
            let checked = $(this).is(':checked');

            Utils.ajax(`${Menus.baseUrl()}/toggle-status/${value}?status=${checked}`)
                .then(() => Utils.notify('', 'Update status menu berhasil'))
                .catch(() => Utils.notify('', 'Gagal memperbarui status menu'));
        })

        options?.renderComplete?.call(this);
    },
    loadTable(options) {
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
                                    <input type="checkbox" class="toggle-switch__checkbox menu-status-switch" value="${row.id}" ${row.status === 'AVAILABLE' ? 'checked' : ''}>
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

        let datatableButtons = `
            <div class="dataTables_buttons actions">
                <span class="actions__item zmdi zmdi-plus" data-table-action="create" title="" data-toggle="tooltip" data-placement="top" data-original-title="Create Record"></span>
                <span class="actions__item zmdi zmdi-refresh" data-table-action="reload" title="" data-toggle="tooltip" data-placement="top" data-original-title="Reload"></span>
                <span class="actions__item zmdi zmdi-cloud-upload" data-table-action="import" title="" data-toggle="tooltip" data-placement="top" data-original-title="Import Menu"></span>
            </div>
        `
        this.table = Utils.datatable('.base-datatable', {
            ajax: {
                url: `${this.baseUrl()}/datatable`,
            },
            columns,
            order: [[6, 'desc']],
            ...(options?.datatableOptions ?? {})
        }, datatableButtons);

        // Data table button actions
        $('body').on('click', '[data-table-action]', function (e) {
            e.preventDefault();

            let action = $(this).data('table-action');

            if (action === 'create') {
                window.location.href = `${Menus.baseUrl()}/create`;
            }
            if (action === 'reload') {
                Menus.reloadTable();
            }
            if (action === 'import') {
                Menus.import();
            }
        });
    },
    reloadTable() {
        this.table.ajax.reload();
    },
    delete(id) {
        Swal({
            title: 'Are you sure?',
            text: 'You won\'t be able to revert this!',
            type: 'warning',
            showCancelButton: true,
            showLoaderOnConfirm: true,
            preConfirm: (login) => {
                return fetch(`${Menus.baseUrl()}/${id}`, {
                    method: 'DELETE',
                    credentials: "same-origin",
                    headers: {
                        'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
                    }
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(response.statusText)
                        }
                        return response.json()
                    })
                    .catch(error => {
                        Swal.showValidationError(
                            `Request failed: ${error}`
                        )
                    })
            },
            allowOutsideClick: false
        }).then((result) => {
            Swal(
                result.value.status ? 'Deleted!' : 'Failed',
                result.value.message,
                result.value.status ? 'success' : 'error'
            );

            if (result.value.status) {
                Menus.reloadTable();
            }
        });
    },
    import() {
        let modal = Utils.loadModal('lg', true);
        let title = modal.find('.modal-title');
        let body = modal.find('.modal-body');
        let options = $('#merchant-filter').html();

        title.text('Menu Import');

        body.html(`
            <form action="">
                <div class="form-group">
                    <label>Merchant</label>
                    <select class="select2" name="merchant">${options}</select>
                </div>
                <div class="form-group">
                    <label>Menu Template</label>
                    <input type="file" class="form-control" placeholder="David Smith" name="spreadsheet" />
                </div>
                <button type="submit" class="btn-primary btn-block btn--submit">Import</button>
            </form>
        `);


        $('.select2').select2({
            width: '100%'
        });

        body.on('submit', 'form', function (e) {
            e.preventDefault();
            let form = $(this);
            let formData = new FormData(form[0]);

            Utils.disableSubmit(form);

            Utils.ajaxFile('/manage/menus/import', 'POST', 'json', formData)
                .fail(xhr => {
                    if (xhr?.responseJSON?.message) {
                        Utils.formFailed(form, xhr.responseJSON.message);
                    }
                })
                .then(results => {
                    Utils.notify('', results.message);
                    Utils.enableSubmit(form);
                    Menus.reloadTable();
                    modal.modal('hide');
                });
        });
    }
}

export default Menus;
