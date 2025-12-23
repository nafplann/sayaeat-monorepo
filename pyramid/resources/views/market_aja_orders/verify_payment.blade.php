<form action="{{ url('manage/market-aja/update/' . $order->id . '?action=payment-verified') }}" method="POST">
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label>Paid at</label>
                <input type="text" class="form-control" readonly
                       value="{{ $order->paid_at }}">
                <i class="form-group__bar"></i>
            </div>

            <div class="form-group">
                <label>Payment Proof</label><br>
                <div class="text-center">
                    @if (str_contains($order->payment_proof_path, '.pdf'))
                        <embed src="{{ asset(Storage::url($order->payment_proof_path)) }}" width="100%" height="640px"/>
                    @else
                        <img src="{{ asset(Storage::url($order->payment_proof_path)) }}"
                             style="max-height: 500px;">
                    @endif
                </div>
            </div>

            <button type="submit" class="btn btn-success btn-block btn--submit">Pembayaran Diterima</button>
        </div>
    </div>
</form>
