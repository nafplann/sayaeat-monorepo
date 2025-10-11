<form class="base-form" action="{{ url('/manage/settings') }}" method="POST" data-is-editing="1">
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
            <div class="form-group required mb-0">
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
    <button type="submit" class="btn btn-outline-success btn-block btn--submit mt-4">UPDATE</button>
</form>
