@use('App\Enums\OrderPaymentMethod')
@use('App\Enums\KirimAja\OrderPaymentMethod', 'KirimAjaPaymentMethod')

@extends('layouts.app')

@section('title', 'Settings')

@section('content')
    <div class="card">
        <form class="base-form" action="{{ url('/manage/settings') }}" method="POST" data-is-editing="1">
            <div class="tab-container">
                <ul class="nav nav-tabs nav-fill" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#makan-aja" role="tab">Makan-Aja</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#belanja-aja" role="tab">Belanja-Aja</a>
                    </li>
                </ul>

                <div class="tab-content px-4 pt-0">
                    <div class="tab-pane active show fade pt-4" id="makan-aja" role="tabpanel">
                        <h2 class="card-title">Pengaturan Umum</h2>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Status Operasional</label>
                                    <select class="select2 form-control" name="operational_status">
                                        <option
                                            value="OPEN" {{ $settings->operational_status === 'OPEN' ? 'selected' : '' }}>
                                            BUKA
                                        </option>
                                        <option
                                            value="CLOSED" {{ $settings->operational_status === 'CLOSED' ? 'selected' : '' }}>
                                            TUTUP
                                        </option>
                                    </select>
                                    <i class="form-group__bar"></i>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group required">
                                    <label>Jarak Maksimum (km)</label>
                                    <input type="text" class="form-control" name="maximum_covered_distance"
                                           value="{{ $settings->maximum_covered_distance }}">
                                    <i class="form-group__bar"></i>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group required">
                                    <label>Maksimum Ongoing Order per Customer</label>
                                    <input type="text" class="form-control" name="maximum_ongoing_orders_per_customer"
                                           value="{{ $settings->maximum_ongoing_orders_per_customer }}">
                                    <i class="form-group__bar"></i>
                                </div>
                            </div>
                        </div>
                        <h2 class="card-title">Ongkos Kirim</h2>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group required">
                                    <label>Ongkos Kirim Minimum</label>
                                    <input type="text" class="form-control" name="minimum_delivery_fee"
                                           value="{{ $settings->minimum_delivery_fee }}">
                                    <i class="form-group__bar"></i>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group required">
                                    <label>Ongkos Kirim (per km)</label>
                                    <input type="text" class="form-control" name="delivery_fee"
                                           value="{{ $settings->delivery_fee }}">
                                    <i class="form-group__bar"></i>
                                </div>
                            </div>
                        </div>
                        <h2 class="card-title">Biaya Layanan</h2>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group required">
                                    <label>Biaya Layanan Minimum</label>
                                    <input type="text" class="form-control" name="minimum_service_fee"
                                           value="{{ $settings->minimum_service_fee }}">
                                    <i class="form-group__bar"></i>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group required mb-0">
                                    <label>Biaya Layanan (per km)</label>
                                    <input type="text" class="form-control" name="service_fee"
                                           value="{{ $settings->service_fee }}">
                                    <i class="form-group__bar"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade pt-4" id="belanja-aja" role="tabpanel">
                        <h2 class="card-title">Pengaturan Umum</h2>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group required">
                                    <label>Jarak Maksimum (km)</label>
                                    <input type="text" class="form-control" name="ba_maximum_covered_distance"
                                           value="{{ $settings->ba_maximum_covered_distance }}">
                                    <i class="form-group__bar"></i>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group required">
                                    <label>Maksimum Ongoing Order per Customer</label>
                                    <input type="text" class="form-control"
                                           name="ba_maximum_ongoing_orders_per_customer"
                                           value="{{ $settings->ba_maximum_ongoing_orders_per_customer }}">
                                    <i class="form-group__bar"></i>
                                </div>
                            </div>
                        </div>
                        <h2 class="card-title">Ongkos Kirim</h2>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group required">
                                    <label>Ongkos Kirim Minimum</label>
                                    <input type="text" class="form-control" name="ba_minimum_delivery_fee"
                                           value="{{ $settings->ba_minimum_delivery_fee }}">
                                    <i class="form-group__bar"></i>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group required">
                                    <label>Ongkos Kirim (per km)</label>
                                    <input type="text" class="form-control" name="ba_delivery_fee"
                                           value="{{ $settings->ba_delivery_fee }}">
                                    <i class="form-group__bar"></i>
                                </div>
                            </div>
                        </div>
                        <h2 class="card-title">Biaya Layanan</h2>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group required">
                                    <label>Biaya Layanan Minimum</label>
                                    <input type="text" class="form-control" name="ba_minimum_service_fee"
                                           value="{{ $settings->ba_minimum_service_fee }}">
                                    <i class="form-group__bar"></i>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group required">
                                    <label>Biaya Layanan (per km)</label>
                                    <input type="text" class="form-control" name="ba_service_fee"
                                           value="{{ $settings->ba_service_fee }}">
                                    <i class="form-group__bar"></i>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group required mb-0">
                                    <label>Profit Margin (%)</label>
                                    <input type="number" class="form-control" name="ba_profit_margin_percentage"
                                           value="{{ $settings->ba_profit_margin_percentage }}" min="1" max="100">
                                    <i class="form-group__bar"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="px-4 pb-4">
                <button type="submit" class="btn btn-outline-primary btn--submit">Update</button>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-body">
            <h2 class="card-title">Metode Pembayaran</h2>
            <form
                class="base-form"
                action="{{ url('/manage/settings') }}"
                method="POST"
                enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group mb-2 d-flex">
                            <div class="toggle-switch">
                                <input type="checkbox" class="toggle-switch__checkbox" id="payment-method-wallet" {{ $settings->payment_method->{OrderPaymentMethod::WALLET->name} ? 'checked' : '' }}>
                                <input type="hidden" name="payment_method[{{ OrderPaymentMethod::WALLET->name }}]" value="{{ $settings->payment_method->{OrderPaymentMethod::WALLET->name} }}">
                                <i class="toggle-switch__helper"></i>
                            </div>
                            <label class="ml-2" for="payment-method-wallet">Wallet</label>
                        </div><div class="form-group mb-2 d-flex">
                            <div class="toggle-switch">
                                <input type="checkbox" class="toggle-switch__checkbox" id="payment-method-cod" {{ $settings->payment_method->{OrderPaymentMethod::CASH_ON_DELIVERY->name} ? 'checked' : '' }}>
                                <input type="hidden" name="payment_method[{{ OrderPaymentMethod::CASH_ON_DELIVERY->name }}]" value="{{ $settings->payment_method->{OrderPaymentMethod::CASH_ON_DELIVERY->name} }}">
                                <i class="toggle-switch__helper"></i>
                            </div>
                            <label class="ml-2" for="payment-method-cod">COD</label>
                        </div>
                        <div class="form-group mb-2 d-flex">
                            <div class="toggle-switch">
                                <input type="checkbox" class="toggle-switch__checkbox" id="payment-method-tf" {{ $settings->payment_method->{OrderPaymentMethod::BANK_TRANSFER->name} ? 'checked' : '' }}>
                                <input type="hidden" name="payment_method[{{ OrderPaymentMethod::BANK_TRANSFER->name }}]" value="{{ $settings->payment_method->{OrderPaymentMethod::BANK_TRANSFER->name} }}">
                                <i class="toggle-switch__helper"></i>
                            </div>
                            <label class="ml-2" for="payment-method-tf">Transfer Bank</label>
                        </div>
                        <div class="form-group mb-2 d-flex">
                            <div class="toggle-switch">
                                <input type="checkbox" class="toggle-switch__checkbox" id="payment-method-qris" {{ $settings->payment_method->{OrderPaymentMethod::QRIS->name} ? 'checked' : '' }}>
                                <input type="hidden" name="payment_method[{{ OrderPaymentMethod::QRIS->name }}]" value="{{ $settings->payment_method->{OrderPaymentMethod::QRIS->name} }}">
                                <i class="toggle-switch__helper"></i>
                            </div>
                            <label class="ml-2" for="payment-method-qris">QRIS</label>
                        </div><div class="form-group mb-2 d-flex">
                            <div class="toggle-switch">
                                <input type="checkbox" class="toggle-switch__checkbox" id="payment-method-cash-sender" {{ $settings->payment_method->{KirimAjaPaymentMethod::CASH_BY_SENDER->name} ? 'checked' : '' }}>
                                <input type="hidden" name="payment_method[{{ KirimAjaPaymentMethod::CASH_BY_SENDER->name }}]" value="{{ $settings->payment_method->{KirimAjaPaymentMethod::CASH_BY_SENDER->name} }}">
                                <i class="toggle-switch__helper"></i>
                            </div>
                            <label class="ml-2" for="payment-method-cash-sender">Cash By Sender</label>
                        </div>
                        <div class="form-group d-flex">
                            <div class="toggle-switch">
                                <input type="checkbox" class="toggle-switch__checkbox" id="payment-method-cash-recipient" {{ $settings->payment_method->{KirimAjaPaymentMethod::CASH_BY_RECIPIENT->name} ? 'checked' : '' }}>
                                <input type="hidden" name="payment_method[{{ KirimAjaPaymentMethod::CASH_BY_RECIPIENT->name }}]" value="{{ $settings->payment_method->{KirimAjaPaymentMethod::CASH_BY_RECIPIENT->name} }}">
                                <i class="toggle-switch__helper"></i>
                            </div>
                            <label class="ml-2" for="payment-method-cash-recipient">Cash By Recipient</label>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-outline-primary btn--submit">Update</button>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        Base.addEdit();

        $(document).ready(function() {
           $('.toggle-switch__checkbox').change(function() {
               const elem = $(this);
               const checked = elem.is(':checked');
               const input = elem.closest('.toggle-switch').find('input[type="hidden"]');
               input.val(checked ? 1 : 0);
           });
        });
    </script>
@endsection
