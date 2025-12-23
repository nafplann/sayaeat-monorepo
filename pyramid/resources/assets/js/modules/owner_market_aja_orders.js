const OwnerMarketAjaOrders = {
    index() {
        let formatter = new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0
        });

        let activeOrders = $('#active-orders');
        let completedOrders = $('#completed-orders');

        function getActiveOrders() {
            Utils.ajax(Utils.baseUrl('manage/store-orders/list?statusCategory=activeForMerchant'), 'GET', 'application/json', {}, {
                success: function (response) {
                    activeOrders.empty();

                    response.data.forEach((order) => {
                        let items = '';

                        let pendingActions = `
                            <div class="row">
                                <div class="col">
                                    <button class="btn btn-danger btn-block order-rejected" data-id="${order.id}">Tolak</button>
                                </div>
                                <div class="col">
                                    <button class="btn btn-success btn-block order-accepted" data-id="${order.id}">Terima</button>
                                </div>
                            </div>`;

                        let readyActions = `<button class="btn btn-success btn-block order-ready" data-id="${order.id}">Pesanan Siap</button>`;

                        let orderActions = '';

                        if (order.status === 3) {
                            orderActions = pendingActions;
                        } else if (order.status === 5) {
                            orderActions = readyActions;
                        }

                        if (order.items.length > 0) {
                            order.items.forEach((item) => {
                                items += `
                                    <li>
                                        (${item.quantity}x)&nbsp; ${item.name}
                                        ${item.addons ? `<br><span class="text-info">${item.addons}</span>` : ''}
                                        ${item.remark ? `<br><span class="text-danger">Catatan: ${item.remark}</span>` : ''}
                                    </li>
                                `;
                            });
                        }

                        activeOrders.append(`
                            <div class="card mb-4">
                                <div class="card-body p-0">
                                    <div class="px-4 pt-4 pb-0">
                                        <div class="row">
                                            <div class="col text-center">
                                                <h5>${order.store.name}</h5>
                                                <p>${moment(order.created_at).fromNow()}</p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col">
                                                <h5>#${order.order_number}</h5>
                                            </div>
                                            <div class="col text-right">
                                                <h5 class="text-danger">${formatter.format(order.subtotal)}</h5>
                                            </div>
                                        </div>
                                    </div>
                                    <ul class="price-table__info py-0">${items}</ul>
                                </div>
                                <div class="card-footer p-4">${orderActions}</div>
                            </div>
                        `);
                    });

                    if (response.data.length === 0) {
                        activeOrders.append(`
                            <div class="card">
                                <div class="card-body text-center">
                                    Tidak ada pesanan aktif.
                                </div>
                            </div>
                        `);
                    }
                }
            });
        }

        function getCompletedOrders() {
            Utils.ajax(Utils.baseUrl('manage/store-orders/list?statusCategory=completed'), 'GET', 'application/json', {}, {
                success: function (response) {
                    completedOrders.empty();

                    response.data.forEach((order) => {
                        let items = '';

                        let pendingActions = `
                            <div class="row">
                                <div class="col">
                                    <button class="btn btn-success btn-block order-accepted" data-id="${order.id}">Terima</button>
                                </div>
                            </div>`;

                        let readyActions = `<button class="btn btn-success btn-block order-ready" data-id="${order.id}">Pesanan Siap</button>`;

                        if (order.items.length > 0) {
                            order.items.forEach((item) => {
                                items += `
                                    <li>
                                        (${item.quantity}x)&nbsp; ${item.name}
                                        ${item.addons ? `<br><span class="text-info">${item.addons}</span>` : ''}
                                        ${item.remark ? `<br><span class="text-danger">Catatan: ${item.remark}</span>` : ''}
                                    </li>
                                `;
                            });
                        }

                        completedOrders.append(`
                            <div class="card mb-4">
                                <div class="card-body p-0">
                                    <div class="px-4 pt-4 pb-0">
                                        <div class="row">
                                            <div class="col text-center">
                                                <h5>${order.store.name}</h5>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col">
                                                <h5>#${order.order_number}</h5>
                                            </div>
                                            <div class="col text-right">
                                                <h5 class="text-danger">${formatter.format(order.subtotal)}</h5>
                                            </div>
                                        </div>
                                    </div>
                                    <ul class="price-table__info py-0">${items}</ul>
                                </div>
                                <div class="card-footer p-4">
                                    ${order.status === 3 ? pendingActions : ''}
                                    ${order.status === 5 ? readyActions : ''}
                                </div>
                            </div>
                        `);
                    });
                }
            });
        }

        $(document).on('click', '.order-rejected', function (e) {
            e.preventDefault();
            let button = $(this);
            let id = button.data('id');

            Swal({
                title: 'Apakah anda yakin membatalkan pesanan ini?',
                text: 'Pesanan ini tidak bisa diubah lagi setelah diproses!',
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
                confirmButtonText: "Ya, Tolak!",
                preConfirm: (reason) => {
                    Utils.disable(button);

                    Utils.ajax(Utils.baseUrl(`manage/store-orders/reject/${id}?reason=${reason}`), 'POST', 'application/json', {}, {
                        success: function (response) {
                            if (response.status) {
                                Utils.notify('', response.message);
                                setTimeout(() => {
                                    window.location.reload();
                                }, 1500);
                            } else {
                                Utils.notify('', response.message);
                            }
                        },
                        error: function (response) {
                            Utils.notify('', response.responseJSON?.message);
                        },
                        complete: function () {
                            Utils.enable(button);
                        }
                    });
                },
                allowOutsideClick: false
            });
        });

        $(document).on('click', '.order-accepted', function (e) {
            e.preventDefault();
            let button = $(this);
            let id = button.data('id');

            Swal({
                title: 'Apakah anda yakin menerima pesanan ini?',
                text: 'Pesanan ini tidak bisa diubah lagi setelah diproses!',
                type: 'warning',
                showCancelButton: true,
                showLoaderOnConfirm: true,
                confirmButtonText: "Ya, Terima!",
                confirmButtonColor: "#32c787",
                preConfirm: () => {
                    Utils.disable(button);

                    Utils.ajax(Utils.baseUrl(`manage/store-orders/process/${id}?action=order-accepted`), 'POST', 'application/json', {}, {
                        success: function (response) {
                            if (response.status) {
                                Utils.notify('', response.message);
                                setTimeout(() => {
                                    window.location.reload();
                                }, 1500);
                            } else {
                                Utils.notify('', response.message);
                            }
                        },
                        error: function (response) {
                            Utils.notify('', response.responseJSON?.message);
                        },
                        complete: function () {
                            Utils.enable(button);
                        }
                    });
                },
                allowOutsideClick: false
            });
        });

        $(document).on('click', '.order-ready', function (e) {
            e.preventDefault();
            let button = $(this);
            let id = button.data('id');

            Swal({
                title: 'Apakah anda yakin?',
                text: 'Pesanan ini tidak bisa diubah lagi setelah diproses!',
                type: 'warning',
                showCancelButton: true,
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    Utils.disable(button);

                    Utils.ajax(Utils.baseUrl(`manage/store-orders/process/${id}?action=order-ready`), 'POST', 'application/json', {}, {
                        success: function (response) {
                            if (response.status) {
                                Utils.notify('', response.message);
                                setTimeout(() => {
                                    window.location.reload();
                                }, 1500);
                            } else {
                                Utils.notify('', response.message);
                            }
                        },
                        error: function (response) {
                            Utils.notify('', response.responseJSON?.message);
                        },
                        complete: function () {
                            Utils.enable(button);
                        }
                    });
                },
                allowOutsideClick: false
            });
        });

        getActiveOrders();
        getCompletedOrders();

        // TODO: Find a better way to handle this (maybe websocket?)
        setInterval(() => {
            getActiveOrders();
        }, 15000);

        setInterval(() => {
            getCompletedOrders();
        }, 60000);
    },
}

export default OwnerMarketAjaOrders;
