@extends('layouts.app')

@section('title', ucwords($module))

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="table-responsive table-hover">
                <table class="table table-bordered kirim-aja-datatable" data-url="{{ $baseUrl }}">
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
            <div id="anu"></div>
            <button class="btn btn-danger btn--action" id="kirim-settings-button"><i class="zmdi zmdi-settings"></i></button>
        </div>
    </div>
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
        KirimAja.index();

        $(document).ready(function () {
            moment.locale('id');
        });
    </script>
@endsection
