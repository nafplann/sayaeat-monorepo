@use(App\Utils\KirimAjaOrderUtil)
@use(App\Enums\KirimAja\OrderPaymentMethod)
@use(App\Enums\KirimAja\OrderStatus)


<div class="invoice pt-0">
    @if($order->status === OrderStatus::CANCELED->value)
        <div class="alert alert-danger mb-5" role="alert">
            Dibatalkan Oleh: {{ $order->canceled_from }} <br>
            Alasan Batal: {{ $order->canceled_reason }}
        </div>
    @endif

    <div class="row invoice__attrs">
        <div class="col">
            <div class="invoice__attrs__item">
                <small>Order Number</small>
                <h3>{{ $order->order_number }}</h3>
            </div>
        </div>

        <div class="col">
            <div class="invoice__attrs__item">
                <small>Date</small>
                <h3>{{ $order->created_at->setTimezone('Asia/Jayapura')->format('d-m-Y H:i:s') }}</h3>
            </div>
        </div>

        <div class="col">
            <div class="invoice__attrs__item">
                <small>Customer</small>
                <h3>{{ $order->customer ? $order->customer->name : '' }}
                    ({{ $order->customer ? $order->customer->phone_number : '' }})</h3>
            </div>
        </div>

        <div class="col">
            <div class="invoice__attrs__item">
                <small>Status</small>
                <h3>{{ KirimAjaOrderUtil::getStatusText(OrderStatus::from($order->status)) }}</h3>
            </div>
        </div>

        <div class="col">
            <div class="invoice__attrs__item">
                <small>Payment Method</small>
                <h3>{{ KirimAjaOrderUtil::getPaymentMethodText(OrderPaymentMethod::from($order->payment_method)) }}</h3>
            </div>
        </div>
    </div>

    <div class="invoice__remarks">
        <div class="row">
            <div class="col-md-6">
                <h5>Order ID</h5>
                <p>{{ $order->id }}</p>

                <h5>Driver</h5>
                <p>{{ $order->driver ? $order->driver->name : '-' }}</p>

                <h5>Distance</h5>
                <p>{{ $order->distance }} km</p>
            </div>
            <div class="col-md-6">
                <h5 class="mt-3">Pengirim</h5>
                <p>{{ $order->sender_name }} ({{ $order->sender_phone }})</p>

                <h5>Penerima</h5>
                <p>{{ $order->recipient_name }} ({{ $order->recipient_phone }})</p>

                <h5>Detail Barang</h5>
                <p>{{ $order->item_details }} ({{ $order->item_weight }} kg)</p>
            </div>
        </div>
    </div>

    <div id="map" style="width: 100%; height: 480px;"></div>
    <div id="map-data" hidden>
        <span id="sender-address">{{ $order->sender_address }}</span>
        <span id="sender-latitude">{{ $order->sender_latitude }}</span>
        <span id="sender-longitude">{{ $order->sender_longitude }}</span>

        @foreach($order->destinations as $destination)
            <span class="recipient-id">{{ $destination->id }}</span>
            <span class="recipient-address">{{ $destination->address }}</span>
            <span class="recipient-latitude">{{ $destination->latitude }}</span>
            <span class="recipient-longitude">{{ $destination->longitude }}</span>
        @endforeach
    </div>
</div>
