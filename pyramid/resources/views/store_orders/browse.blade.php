@extends('layouts.app')

@section('title', ucwords($module))

@section('content')
    <div class="card">
        <div class="card-body">
            <h2 class="card-title">Filters</h2>
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Merchant</label>
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
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Service</label>
                        <select class="select2" multiple data-placeholder="Select one or more service" name="service_filter" id="service-filter">
                            <option value="MAKAN_AJA">Makan Aja</option>
                            <option value="BELANJA_AJA">Belanja Aja</option>
                            <option value="KIRIM_AJA">Kirim Aja</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Status</label>
                        <select class="select2" multiple data-placeholder="Select order status" name="status_filter" id="status-filter">
                            <option value="MAKAN_AJA">Makan Aja</option>
                            <option value="BELANJA_AJA">Belanja Aja</option>
                            <option value="KIRIM_AJA">Kirim Aja</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Payment Status</label>
                        <select class="select2" multiple data-placeholder="Select payment status" name="payment_status_filter" id="payment-status-filter">
                            <option value="MAKAN_AJA">Makan Aja</option>
                            <option value="BELANJA_AJA">Belanja Aja</option>
                            <option value="KIRIM_AJA">Kirim Aja</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered base-datatable" data-url="{{ $baseUrl }}">
                    <thead>
                    <tr>
                        <th data-column="order_number">Order Number</th>
                        <th data-column="merchant">Merchant</th>
                        <th data-column="subtotal">Subtotal</th>
                        <th data-column="delivery_fee">Delivery Fee</th>
                        <th data-column="service_fee">Service Fee</th>
                        <th data-column="total">Total</th>
                        <th data-column="distance">Distance</th>
                        <th data-column="status">Status</th>
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
        const merchantFilter = $('#merchant-filter');

        Base.index({
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
                Base.reloadTable();
            })
        });
    </script>
@endsection
