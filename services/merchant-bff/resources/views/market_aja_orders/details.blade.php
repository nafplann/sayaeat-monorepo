@use(App\Enums\OrderPaymentMethod)
@use(App\Utils\MarketAjaOrderUtil)
@use(App\Enums\MarketAja\OrderStatus)


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
                <h3>{{ MarketAjaOrderUtil::getStatusText(OrderStatus::from($order->status)) }}</h3>
            </div>
        </div>

        <div class="col">
            <div class="invoice__attrs__item">
                <small>Payment Method</small>
                <h3>{{ MarketAjaOrderUtil::getPaymentMethodText(OrderPaymentMethod::from($order->payment_method)) }}</h3>
            </div>
        </div>
    </div>

    <div class="invoice__remarks">
        <div class="row">
            <div class="col-md-4">
                <h5>Order ID</h5>
                <p>{{ $order->id }}</p>

                <h5>Driver</h5>
                <p>{{ $order->driver ? $order->driver->name : '-' }}</p>

                <h5>Distance</h5>
                <p>{{ $order->distance }} km</p>

                <h5>Note to Driver</h5>
                <p>{{ $order->note_to_driver ?? '-' }}</p>
            </div>
            <div class="col-md-4">
                <h5 class="mt-3">Store</h5>
                <p>{{ $order->store->name }}</p>

                <h5>Bank Name</h5>
                <p>{{ $order->store->bank_name ?? '-' }}</p>

                <h5>Bank Account Number</h5>
                <p>{{ $order->store->bank_account_number ?? '-' }}
                    an {{ $order->store->bank_account_holder ?? '-' }}</p>

                <h5>QRIS Link</h5>
                <p>{{ $order->store->qris_link ?? '-' }}</p>
            </div>
            <div class="col-md-4">
                @if($order->payment_proof_path)
                    <h5>Bukti Pembayaran</h5>
                    <img src="{{ asset(Storage::url($order->payment_proof_path)) }}"
                         style="max-height: 320px;">
                @endif
            </div>
        </div>
    </div>

    <div class="overflow-auto">
        <table class="table table-bordered invoice__table table-hover mt-4">
            <thead>
            <tr class="text-uppercase">
                <th>ITEM</th>
                <th>PRICE</th>
                <th>QUANTITY</th>
                <th>TOTAL</th>
            </tr>
            </thead>
            <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td style="width: 50%">
                        {{ $item->name }}
                        @if ($item->remark)
                            <br>
                            <small>Remark: {{ $item->remark }}</small>
                        @endif
                    </td>
                    <td>{{ display_price($item->price) }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ display_price($item->total) }}</td>
                </tr>
            @endforeach

            <tr>
                <td colspan="3">Subtotal</td>
                <td>{{ display_price($order->subtotal - $order->items->sum('markup_amount')) }}</td>
            </tr>

            <tr>
                <td colspan="3">Delivery Fee</td>
                <td>{{ display_price($order->delivery_fee) }}</td>
            </tr>

            <tr>
                <td colspan="3">Service Fee</td>
                <td>{{ display_price($order->service_fee) }}</td>
            </tr>

            <tr>
                <td colspan="3">Product Markup</td>
                <td>{{ display_price($order->items->sum('markup_amount')) }}</td>
            </tr>

            <tr>
                <td colspan="3" class="text-danger">Diskon</td>
                <td class="text-danger">{{ display_price($order->delivery_fee_discount + $order->order_discount) }}</td>
            </tr>

            <tr>
                <td colspan="3" class="font-weight-bold">Grand Total</td>
                <td class="font-weight-bold">{{ display_price($order->total_after_discount) }}</td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
