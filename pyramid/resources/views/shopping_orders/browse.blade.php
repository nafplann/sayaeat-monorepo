@extends('layouts.app')

@section('title', ucwords($module))

@section('content')
    @role('super admin')
    <div class="card">
        <div class="card-body">
            <h2 class="card-title">Filters</h2>
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="mb-0">Status</label>
                        <select class="select form-control filters" name="status" multiple>
                            <option value="0">DRAFT</option>
                            <option value="1">ONGOING</option>
                            <option value="2">COMPLETED</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="mb-0">Payment Method</label>
                        <select class=" select form-control filters" name="payment_method" multiple>
                            <option value="1">QRIS</option>
                            <option value="2">TRANSFER</option>
                            <option value="3">TUNAI</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="mb-0">Payment Status</label>
                        <select class=" select form-control filters" name="payment_status" multiple>
                            <option value="0">BELUM BAYAR</option>
                            <option value="1">LUNAS</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group mb-0">
                        <label>Order Date</label>
                        <div class="input-group mb-0">
                            <input type="text" class="form-control pl-0 filters" name="date_range"
                                   readonly value="">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="zmdi zmdi-calendar"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endrole

    <div class="card">
        <div class="card-body">
            <div class="table-responsive table-hover">
                <table class="table table-bordered base-datatable" data-url="{{ $baseUrl }}">
                    <thead>
                    <tr>
                        <th data-column="customer_name">Customer</th>
                        <th data-column="customer_phone">Phone</th>
                        <th data-column="distance">Distance</th>
                        <th data-column="subtotal">Subtotal</th>
                        <th data-column="service_fee">Service Fee</th>
                        <th data-column="delivery_fee">Delivery Fee</th>
                        <th data-column="total">Total</th>
                        <th data-column="driver">Driver</th>
                        <th data-column="status" class="text-center">Status</th>
                        <th data-column="paid_by">Paid By</th>
                        <th data-column="payment_method">Payment Method</th>
                        <th data-column="payment_status">Payment Status</th>
                        <th data-column="created_at" class="text-center">Created At</th>
                        <th data-column="updated_at" class="text-center">Updated At</th>
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
        function getFiltersValue() {
            let statusFilter = $('select[name="status"]');
            let paymentMethod = $('select[name="payment_method"]');
            let paymentStatus = $('select[name="payment_status"]');
            let dateRange = $('input[name="date_range"]');

            let filters = [
                {name: 'status', value: statusFilter.val()},
                {name: 'payment_method', value: paymentMethod.val()},
                {name: 'payment_status', value: paymentStatus.val()},
                {name: 'date_range', value: dateRange.attr('data-raw-value') ?? ''},
            ];

            return filters.reduce((prev, next) => {
                if (!next.value || !next.value.length) return prev;

                return {
                    ...prev,
                    [next.name]: next.value
                };
            }, {});
        }

        Base.index({
            datatableOptions: {
                ajax: {
                    url: `${Base.baseUrl()}/datatable`,
                    data: (existing) => ({
                        ...existing,
                        ...getFiltersValue()
                    })
                },
                order: [[12, 'desc']],
            },
            customActions: [
                {
                    title: 'Whatsapp',
                    icon: 'zmdi zmdi-whatsapp',
                    className: 'action-whatsapp'
                },
            ]
        });

        $(document).ready(function () {
            $(document).on('change', '.filters', function () {
                Base.reloadTable();
            });

            $(document).on('click', '.action-whatsapp', function () {
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
        });

        $(function () {
            function cb(start, end) {
                $('input[name="date_range"]').val(start.format('D MMMM YYYY') + ' - ' + end.format('D MMMM YYYY'))
                    .attr('data-raw-value', `${start.format('YYYY-MM-DD')};${end.format('YYYY-MM-DD')}`)
                    .trigger('change');
            }

            $('input[name="date_range"]').daterangepicker({
                startDate: moment().subtract(29, 'days'),
                endDate: moment(),
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                },
                autoUpdateInput: false,
                locale: {
                    cancelLabel: 'Clear'
                }

            }, cb);
        });

    </script>
@endsection
