@extends('layouts.app')

@section('title', 'Ongoing Orders')

@section('content')
    <div class="card">
        <div class="card-body">
            <h2 class="card-title">
                BELANJA-AJA!&nbsp;&nbsp;<span class="badge badge-pill badge-danger belanja-aja-count" hidden>0</span>
            </h2>
            <div class="table-responsive table-hover">
                <table class="table table-bordered belanja-aja-datatable" data-url="{{ url('manage/shopping-orders') }}">
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

    <div class="card">
        <div class="card-body">
            <h2 class="card-title">
                KIRIM-AJA!&nbsp;&nbsp;<span class="badge badge-pill badge-danger kirim-aja-count" hidden>0</span>
            </h2>
            <div class="table-responsive table-hover">
                <table class="table table-bordered kirim-aja-datatable" data-url="{{ url('manage/kirim-aja') }}">
                    <thead>
                    <tr>
                        <th data-column="order_number">Order Number</th>
                        <th data-column="distance">Distance</th>
                        <th data-column="service_fee">Service Fee</th>
                        <th data-column="delivery_fee">Delivery Fee</th>
                        <th data-column="total">Total</th>
                        <th data-column="driver">Driver</th>
                        <th data-column="status_text" class="text-center">Status</th>
                        <th data-column="payment_method">Payment Method</th>
                        <th data-column="payment_status_text">Payment Status</th>
                        <th data-column="duration">Duration</th>
                        <th data-column="created_at" class="text-center">Created At</th>
                        <th data-column="_action" data-name="_action" class="text-center">Action</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h2 class="card-title">
                MAKAN-AJA!&nbsp;&nbsp;<span class="badge badge-pill badge-danger makan-aja-count" hidden>0</span>
            </h2>
            <div class="table-responsive table-hover">
                <table class="table table-bordered makan-aja-datatable" data-url="{{ url('manage/makan-aja') }}">
                    <thead>
                    <tr>
                        <th data-column="order_number">Order Number</th>
                        <th data-column="merchant">Merchant</th>
                        <th data-column="customer">Customer</th>
                        <th data-column="distance">Distance</th>
                        <th data-column="menu_markup">Markup</th>
                        <th data-column="subtotal">Subtotal</th>
                        <th data-column="service_fee">Service Fee</th>
                        <th data-column="delivery_fee">Delivery Fee</th>
                        <th data-column="total">Total</th>
                        <th data-column="driver">Driver</th>
                        <th data-column="status_text" class="text-center">Status</th>
                        <th data-column="merchant_paid_by">Paid By</th>
                        <th data-column="payment_method">Payment Method</th>
                        <th data-column="payment_status_text">Payment Status</th>
                        <th data-column="duration">Duration</th>
                        <th data-column="created_at" class="text-center">Created At</th>
                        <th data-column="_action" data-name="_action" class="text-center">Action</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h2 class="card-title">
                MARKET-AJA!&nbsp;&nbsp;<span class="badge badge-pill badge-danger market-aja-count" hidden>0</span>
            </h2>
            <div class="table-responsive table-hover">
                <table class="table table-bordered market-aja-datatable" data-url="{{ url('manage/market-aja') }}">
                    <thead>
                    <tr>
                        <th data-column="order_number">Order Number</th>
                        <th data-column="store">Store</th>
                        <th data-column="customer">Customer</th>
                        <th data-column="distance">Distance</th>
                        <th data-column="product_markup">Markup</th>
                        <th data-column="subtotal">Subtotal</th>
                        <th data-column="service_fee">Service Fee</th>
                        <th data-column="delivery_fee">Delivery Fee</th>
                        <th data-column="total">Total</th>
                        <th data-column="driver">Driver</th>
                        <th data-column="status_text" class="text-center">Status</th>
                        <th data-column="store_paid_by">Paid By</th>
                        <th data-column="payment_method">Payment Method</th>
                        <th data-column="payment_status_text">Payment Status</th>
                        <th data-column="duration">Duration</th>
                        <th data-column="created_at" class="text-center">Created At</th>
                        <th data-column="_action" data-name="_action" class="text-center">Action</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <button class="btn btn-danger btn--action" id="makan-settings-button"><i class="zmdi zmdi-settings"></i></button>
@endsection

@section('styles')
    <style>
        .modal-full {
            min-width: 95%;
        }

        .invoice {
            min-width: 100%;
            max-width: 100%;
            box-shadow: none;
        }
    </style>
@endsection

@section('scripts')
    <script>
        let belanjaAjaTable = $('.belanja-aja-datatable');
        let kirimAjaTable = $('.kirim-aja-datatable');
        let makanAjaTable = $('.makan-aja-datatable');
        let marketAjaTable = $('.market-aja-datatable');

        $(document).ready(function () {
            moment.locale('id');

            BelanjaAja.index({
                tableAutoRefresh: true,
                statusCategory: 'activeForAdmin',
                customActions: [
                    {
                        title: 'Whatsapp',
                        icon: 'zmdi zmdi-whatsapp',
                        className: 'action-whatsapp'
                    },
                ],
                datatableOptions: {
                    dom: '',
                    lengthMenu: [[100], ['100 Rows']],
                    drawCallback: function(settings) {
                        let api = this.api();
                        let count = api.rows().data().length;
                        $('.belanja-aja-count').text(count).attr('hidden', count < 1);
                    }
                }
            });

            KirimAja.index({
                tableAutoRefresh: true,
                statusCategory: 'activeForAdmin',
                datatableOptions: {
                    dom: '',
                    lengthMenu: [[100], ['100 Rows']],
                    drawCallback: function(settings) {
                        let warning = kirimAjaTable.find('.table-warning').length ?? 0;
                        let success = kirimAjaTable.find('.table-success').length ?? 0;
                        let count = warning + success;

                        $('.kirim-aja-count').text(count).attr('hidden', count < 1);
                    }
                }
            });

            MakanAja.index({
                tableAutoRefresh: true,
                statusCategory: 'activeForAdmin',
                datatableOptions: {
                    dom: '',
                    lengthMenu: [[100], ['100 Rows']],
                    drawCallback: function(settings) {
                        let warning = makanAjaTable.find('.table-warning').length ?? 0;
                        let success = makanAjaTable.find('.table-success').length ?? 0;
                        let count = warning + success;

                        $('.makan-aja-count').text(count).attr('hidden', count < 1);
                    }
                }
            });

            MarketAja.index({
                tableAutoRefresh: true,
                statusCategory: 'activeForAdmin',
                datatableOptions: {
                    dom: '',
                    lengthMenu: [[100], ['100 Rows']],
                    drawCallback: function(settings) {
                        let warning = marketAjaTable.find('.table-warning').length ?? 0;
                        let success = marketAjaTable.find('.table-success').length ?? 0;
                        let count = warning + success;

                        $('.market-aja-count').text(count).attr('hidden', count < 1);
                    }
                }
            });
        });
    </script>
@endsection
