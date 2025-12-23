<form action="{{ url('manage/market-aja/update/' . $order->id . '?action=driver-found') }}" method="POST">
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
Layanan : Market-Aja!
Metode Pembayaran : {{ \App\Utils\MarketAjaOrderUtil::getPaymentMethodText(\App\Enums\OrderPaymentMethod::from($order->payment_method)) }}
Lokasi Toko : {{ $order->store->name }} | https://www.google.com/maps/place/{{ $order->store->latitude }},{{ $order->store->longitude }}
Lokasi Drop : https://www.google.com/maps/place/{{ $order->address_latitude }},{{ $order->address_longitude }}
Note untuk Driver : {{ $order->note_to_driver ?? '-' }}
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
                <label>Store Payment By</label>
                <select class="select2 form-control" name="store_paid_by">
                    <option value="Admin">Admin</option>
                    <option value="Driver">Driver</option>
                </select>
                <i class="form-group__bar"></i>
            </div>

            <button type="submit" class="btn btn-success btn-block btn--submit">Tugaskan Driver</button>
        </div>
    </div>
</form>
