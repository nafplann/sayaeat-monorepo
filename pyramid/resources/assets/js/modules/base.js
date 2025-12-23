const Base = {
    baseUrl() {
        return $('.base-datatable').data('url');
    },
    /**
     * @param options
     * @type options {customActions: [], renderComplete: () {}, datatableOptions: {}}
     */
    index(options = {}) {

        Base.loadTable(options);

        let table = $('.base-datatable');

        table.on('click', '.action-edit', function (e) {
            e.preventDefault();
            let value = $(this).data('id');
            window.location.href = `${Base.baseUrl()}/${value}/edit`;
        });

        table.on('click', '.action-delete', function (e) {
            e.preventDefault();
            let value = $(this).data('id');
            Base.delete(value);
        });

        options?.renderComplete?.call(this);
    },
    loadTable(options) {
        let columns = $('.base-datatable').find('thead th').map(function () {
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

        this.table = Utils.datatable('.base-datatable', {
            ajax: {
                url: `${this.baseUrl()}/datatable`,
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
    /**
     * @param options
     * @type options {onSuccess: () {}, onFailed: () {} }
     */
    addEdit(options = {}) {

        $('.base-form').on('submit', async function (e) {
            e.preventDefault();

            let form = $(this);
            let formData = new FormData(form[0]);
            let url = form.attr('action');
            let method = form.attr('method');
            let isEditing = form.data('is-editing') === 1;

            let data = Object.fromEntries(formData);
            let keys = Object.keys(data);

            var options = {
                maxSizeMB: 1,
                maxWidthOrHeight: 1024,
                useWebWorker: false,
                preserveExif: true,
            };

            Utils.disableSubmit(form);

            for (let item of keys) {
                if (data[item] instanceof File && data[item]['size']) {
                    let filename = data[item]['name'];
                    let compressedImage = await imageCompression(data[item], options)
                        .then(function (output) {
                            return output;
                        })
                        .catch(function (error) {
                        });

                    formData.delete(item);
                    formData.append(item, compressedImage, filename);
                    console.log({compressedImage})
                }

                let value = $(`[name="${item}"]`).attr('data-raw-value');

                if (value !== '' && value !== undefined) {
                    formData.delete(item);
                    formData.append(item, value);
                }
            }

            Utils.ajaxFile(url, method, 'json', formData)
                .fail(xhr => {
                    options?.onFailed?.call(this);
                    if (xhr?.responseJSON?.message) {
                        Utils.formFailed(form, xhr.responseJSON.message);
                        return;
                    }
                    Utils.formFailed(form, `Code ${xhr.status}: ${xhr.statusText}`);
                })
                .then(results => {
                    if (!results.status) {
                        Utils.formFailed(form, results.message);
                        return;
                    }
                    Utils.notify('', results.message);
                    Utils.enableSubmit(form);

                    if (!isEditing) form[0].reset();
                    options?.onSuccess?.call(this);
                });
        });
    },
    delete(id) {
        Swal({
            title: 'Are you sure?',
            text: 'You won\'t be able to revert this!',
            type: 'warning',
            showCancelButton: true,
            showLoaderOnConfirm: true,
            preConfirm: (login) => {
                return fetch(`${Base.baseUrl()}/${id}`, {
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
                Base.reloadTable();
            }
        });
    }
}

export default Base;
