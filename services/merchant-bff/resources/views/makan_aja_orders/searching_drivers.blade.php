<form action="{{ url('manage/makan-aja/update/' . $order->id . '?action=driver-found') }}" method="POST">
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label>Message to Driver</label>
                <div class="position-relative">
<pre id="driver-message">
Order {{ $order->order_number }}
Nama : {{ $order->customer->name }}
No WA : {{ $order->customer->phone_number }}
Jarak : {{ $order->distance }} km
Layanan : Makan-Aja!
Metode Pembayaran : {{ \App\Utils\MakanAjaOrderUtil::getPaymentMethodText(\App\Enums\OrderPaymentMethod::from($order->payment_method)) }}
Lokasi Resto : {{ $order->merchant->name }} | https://www.google.com/maps/place/{{ $order->merchant->latitude }},{{ $order->merchant->longitude }}
Lokasi Drop : https://www.google.com/maps/place/{{ $order->address_latitude }},{{ $order->address_longitude }}
Note untuk Driver : {{ $order->note_to_driver ?? '-' }}
-------------------
❇️ Daftar Pesanan ❇️
@foreach($order->items as $item)
{{ $item->quantity }}x {{ $item->name }} {{ $item->note ? '(' . $item->note . ')' : '' }}
@endforeach
Total Pesanan : Rp {{ display_price($order->subtotal - $order->items->sum('markup_amount')) }}
-------------------
Total Bayar : Rp {{ display_price($order->total_after_discount) }}
</pre>
                    <button type="button" class="btn btn-outline-success position-absolute mt-2 mr-2"
                            style="top: 0; right: 0; width: 80px;"
                            onclick="navigator.clipboard.writeText($('#driver-message').html());">COPY
                    </button>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <label>Driver</label>
                <select class="select2 form-control" name="driver_id">
                    <option disabled selected>Pilih Driver</option>
                    @foreach(\App\Models\Driver::all() as $driver)
                        <option
                            value="{{ $driver->id }}">{{ $driver->name }}</option>
                    @endforeach
                </select>
                <i class="form-group__bar"></i>
            </div>

            <div class="form-group">
                <label>Merchant Payment By</label>
                <select class="select2 form-control" name="merchant_paid_by">
                    <option value="Admin">Admin</option>
                    <option value="Driver">Driver</option>
                </select>
                <i class="form-group__bar"></i>
            </div>

            <button type="submit" class="btn btn-success btn-block btn--submit">Tugaskan Driver</button>
        </div>
    </div>
</form>
