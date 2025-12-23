@extends('layouts.app')

@use(App\Enums\InputType)

@php
    $isEditing = isset($data);
@endphp

@section('title', $isEditing ? "Edit $module" : "Create $module")

@section('content')
    <form
        class="base-form"
        action="{{ $isEditing ? "$baseUrl/{$data->id}" : $baseUrl }}"
        method="POST"
        enctype="multipart/form-data"
        data-is-editing={{ $isEditing }}>
        {{ $isEditing ? method_field('PUT') : '' }}

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <div class="form-group required">
                            <label>Customer Name</label>
                            <input type="text" class="form-control" placeholder="" name="customer_name"
                                   value="{{ $isEditing ? $data->customer_name : '' }}">
                            <i class="form-group__bar"></i>
                        </div>
                        <div class="form-group required">
                            <label>Customer Phone</label>
                            <input type="text" class="form-control" placeholder="" name="customer_phone"
                                   value="{{ $isEditing ? $data->customer_phone : '' }}">
                            <i class="form-group__bar"></i>
                        </div>
                        <div class="form-group required">
                            <label>Pickup Location</label>
                            <input type="text" class="form-control" placeholder="" name="pickup_location"
                                   value="{{ $isEditing ? $data->pickup_location : '' }}">
                            <i class="form-group__bar"></i>
                        </div>
                        <div class="form-group required">
                            <label>Drop Location</label>
                            <input type="text" class="form-control" placeholder="" name="drop_location"
                                   value="{{ $isEditing ? $data->drop_location : '' }}">
                            <i class="form-group__bar"></i>
                        </div>
                        <div class="form-group">
                            <label>Driver</label>
                            <select class="select form-control" name="driver_id">
                                <option value="">-</option>
                                @foreach(\App\Models\Driver::all() as $driver)
                                    <option
                                        {{ $isEditing && $data->driver_id === $driver->id ? 'selected' : '' }} value="{{ $driver->id }}">{{ $driver->name }}</option>
                                @endforeach
                            </select>
                            <i class="form-group__bar"></i>
                        </div>
                        <div class="form-group required">
                            <label>Shopping List</label>
                            <textarea
                                class="form-control textarea-autosize"
                                name="shopping_list"
                                rows="8">{{ $isEditing ? $data->shopping_list : '' }}</textarea>
                            <i class="form-group__bar"></i>
                        </div>
                        <div class="form-group required mb-0">
                            <label>Status</label>
                            <select class="select form-control" name="status">
                                <option {{ $isEditing && $data->status === 0 ? 'selected' : '' }} value="0">DRAFT
                                </option>
                                <option {{ $isEditing && $data->status === 1 ? 'selected' : '' }} value="1">ONGOING
                                </option>
                                <option {{ $isEditing && $data->status === 2 ? 'selected' : '' }} value="2">COMPLETED
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div class="form-group required">
                            <label>Distance</label>
                            <input type="text" class="form-control" placeholder="" name="distance"
                                   value="{{ $isEditing ? $data->distance : '' }}">
                            <i class="form-group__bar"></i>
                        </div>
                        <div class="form-group required">
                            <label>Subtotal</label>
                            <input type="text" class="form-control number-mask" placeholder="" name="subtotal"
                                   value="{{ $isEditing ? $data->subtotal : '' }}"
                                   data-raw-value="{{ $isEditing ? $data->subtotal : '' }}">
                            <i class="form-group__bar"></i>
                        </div>
                        <div class="form-group required">
                            <label>Delivery Fee</label>
                            <input type="text" class="form-control number-mask" placeholder="" name="delivery_fee"
                                   value="{{ $isEditing ? $data->delivery_fee : '' }}" disabled>
                            <i class="form-group__bar"></i>
                        </div>
                        <div class="form-group required">
                            <label>Service Fee</label>
                            <input type="text" class="form-control number-mask" placeholder="" name="service_fee"
                                   value="{{ $isEditing ? $data->service_fee : '' }}" disabled>
                            <i class="form-group__bar"></i>
                        </div>
                        <div class="form-group required mb-0">
                            <label>Total</label>
                            <input type="text" class="form-control number-mask" placeholder="" name="total"
                                   value="{{ $isEditing ? $data->total : '' }}" disabled>
                            <i class="form-group__bar"></i>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="form-group">
                            <label>Paid By</label>
                            <select class="select form-control" name="paid_by">
                                <option value="">-</option>
                                <option {{ $isEditing && $data->paid_by === 'KURIR' ? 'selected' : '' }} value="KURIR">
                                    KURIR
                                </option>
                                <option {{ $isEditing && $data->paid_by === 'ADMIN' ? 'selected' : '' }} value="ADMIN">
                                    ADMIN
                                </option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Payment Method</label>
                            <select class="select form-control" name="payment_method">
                                <option value="">-</option>
                                <option {{ $isEditing && $data->payment_method === 1 ? 'selected' : '' }} value="1">QRIS
                                </option>
                                <option {{ $isEditing && $data->payment_method === 2 ? 'selected' : '' }} value="2">
                                    TRANSFER
                                </option>
                                <option {{ $isEditing && $data->payment_method === 3 ? 'selected' : '' }} value="3">
                                    TUNAI
                                </option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Payment Status</label>
                            <select class="select form-control" name="payment_status">
                                <option value="">-</option>
                                <option {{ $isEditing && $data->payment_status === 0 ? 'selected' : '' }} value="0">
                                    BELUM
                                    BAYAR
                                </option>
                                <option {{ $isEditing && $data->payment_status === 1 ? 'selected' : '' }} value="1">
                                    LUNAS
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="pb-4">
            <button type="submit"
                    class="btn btn-outline-primary btn--submit">{{ $isEditing ? 'Update' : 'Submit' }}</button>
            <a href="#" onclick="window.history.back()" class="btn btn-outline-dark btn--back">Go Back</a>
        </div>
    </form>
@endsection

@section('styles')

@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            let distance = $('input[name="distance"]');
            let subtotal = $('input[name="subtotal"]');

            Base.addEdit();

            $(document).on('keyup', [distance, subtotal], $.debounce(250, function (e) {
                Utils.ajax(Utils.baseUrl('manage/shopping-orders/fees'), 'GET', 'application/json', {
                    distance: distance.val(),
                    subtotal: subtotal.val().replaceAll('.', '')
                }, {
                    success: function (response) {
                        $('input[name="delivery_fee"]').val(response.delivery_fee);
                        $('input[name="service_fee"]').val(response.service_fee);
                        $('input[name="total"]').val(response.total);
                    }
                });
            }));
        });
    </script>
@endsection
