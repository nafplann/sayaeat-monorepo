@use(App\Utils\KirimAjaOrderUtil)
@use(App\Enums\KirimAja\OrderStatus)

<form action="{{ url('manage/kirim-aja/update/' . $order->id . '?action=status-update') }}" method="POST">
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
                <label>Status</label>
                <select class="select form-control" name="status">
                    <option disabled selected>Pilih Status</option>
                    @if($order->status < 5)
                        <option value="5">{{ KirimAjaOrderUtil::getStatusText(OrderStatus::from(5)) }}</option>
                    @elseif($order->status < 6)
                        <option value="6">{{ KirimAjaOrderUtil::getStatusText(OrderStatus::from(6)) }}</option>
                    @else
                        <option value="7">{{ KirimAjaOrderUtil::getStatusText(OrderStatus::from(7)) }}</option>
                    @endif
                </select>
                <i class="form-group__bar"></i>
            </div>
            <button type="submit" class="btn btn-success btn-block btn--submit">Update</button>
        </div>
    </div>
</form>
