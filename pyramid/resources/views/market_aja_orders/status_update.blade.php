@use(App\Utils\MarketAjaOrderUtil)
@use(App\Enums\MarketAja\OrderStatus)
@use(App\Enums\OrderPaymentMethod)

<form action="{{ url('manage/market-aja/update/' . $order->id . '?action=status-update') }}" method="POST">
    <div class="row">
        <div class="col-md-12">
            <div class="form-group required">
                <label>Distance</label>
                <input type="text" class="form-control" placeholder="" name="distance"
                       data-subtotal="{{ $order->subtotal }}"
                       value="{{ $order->distance }}">
                <i class="form-group__bar"></i>
            </div>
            <div class="form-group">
                <label>Delivery Fee</label>
                <input type="text" class="form-control" name="delivery_fee" value="{{ $order->delivery_fee }}" disabled>
                <i class="form-group__bar"></i>
            </div>
            <div class="form-group">
                <label>Service Fee</label>
                <input type="text" class="form-control" name="service_fee" value="{{ $order->service_fee }}" disabled>
                <i class="form-group__bar"></i>
            </div>
            <div class="form-group">
                <label>Total</label>
                <input type="text" class="form-control" name="total" value="{{ $order->total }}" disabled>
                <i class="form-group__bar"></i>
            </div>
            <div class="form-group required">
                <label>Metode Pembayaran</label>
                <select class="select form-control" name="payment_method">
                    @foreach(OrderPaymentMethod::cases() as $method)
                        <option
                            value="{{ $method->value }}"
                            {{ $order->payment_method == $method->value ? 'selected' : '' }}
                        >{{ MarketAjaOrderUtil::getPaymentMethodText($method) }}</option>
                    @endforeach
                </select>
                <i class="form-group__bar"></i>
            </div>
            <div class="form-group required">
                <label>Status</label>
                <select class="select form-control" name="status">
                    <option disabled selected>Pilih Status</option>
                    @if($order->status < 8)
                        <option value="8">{{ MarketAjaOrderUtil::getStatusText(OrderStatus::from(8)) }}</option>
                    @else
                        <option value="9">{{ MarketAjaOrderUtil::getStatusText(OrderStatus::from(9)) }}</option>
                    @endif
                </select>
                <i class="form-group__bar"></i>
            </div>
            <button type="submit" class="btn btn-success btn-block btn--submit">Update</button>
        </div>
    </div>
</form>
