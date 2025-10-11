@extends('layouts.app')

@section('title', 'Dashboard')

@section('page_actions')
    <button id="reportrange" class="btn btn-outline-success px-3"><i class="zmdi zmdi-calendar"></i>
        <span class="ml-2"></span>
    </button>
@endsection

@section('content')
    <div id="dashboard-content">
        <div class="row">
            <div class="col-sm-6 col-md-3">
                <a href="{{ url('manage/customers') }}" class="quick-stats__item bg-red quick-overview"
                   data-chart="bar" data-model="customer">
                    <div class="quick-stats__info">
                        <h2 class="count">-</h2>
                        <small>Total Customers</small>
                    </div>

                    <div class="quick-stats__chart sparkline-bar-stats history"></div>
                </a>
            </div>

            <div class="col-sm-6 col-md-3">
                <a href="{{ url('manage/drivers') }}" class="quick-stats__item bg-amber quick-overview" data-chart="bar"
                   data-model="driver">
                    <div class="quick-stats__info">
                        <h2 class="count">-</h2>
                        <small>Total Drivers</small>
                    </div>
                    <div class="quick-stats__chart sparkline-bar-stats history"></div>
                </a>
            </div>

            <div class="col-sm-6 col-md-3">
                <a href="{{ url('manage/merchants') }}" class="quick-stats__item bg-blue quick-overview"
                   data-chart="bar" data-model="merchant">
                    <div class="quick-stats__info">
                        <h2 class="count">-</h2>
                        <small>Total Merchants</small>
                    </div>
                    <div class="quick-stats__chart sparkline-bar-stats history"></div>
                </a>
            </div>

            <div class="col-sm-6 col-md-3">
                <a href="{{ url('manage/menus') }}" class="quick-stats__item bg-purple quick-overview" data-chart="bar"
                   data-model="menu">
                    <div class="quick-stats__info">
                        <h2 class="count">-</h2>
                        <small>Menus</small>
                    </div>
                    <div class="quick-stats__chart sparkline-bar-stats history"></div>
                </a>
            </div>

            <div class="col-md-3">
                <a href="{{ url('manage/shopping-orders') }}" class="quick-stats__item bg-indigo quick-overview"
                   data-chart="bar"
                   data-model="shoppingOrder">
                    <div class="quick-stats__info">
                        <h2 class="count">-</h2>
                        <small>Belanja-Aja</small>
                    </div>
                    <div class="quick-stats__chart sparkline-bar-stats history"></div>
                </a>
            </div>

            <div class="col-md-3">
                <a href="{{ url('manage/kirim-aja') }}" class="quick-stats__item bg-deep-orange quick-overview"
                   data-chart="bar"
                   data-model="shipmentOrder">
                    <div class="quick-stats__info">
                        <h2 class="count">-</h2>
                        <small>Kirim-Aja</small>
                    </div>
                    <div class="quick-stats__chart sparkline-bar-stats history"></div>
                </a>
            </div>

            <div class="col-md-3">
                <a href="{{ url('manage/makan-aja') }}" class="quick-stats__item bg-green quick-overview"
                   data-chart="bar" data-model="order">
                    <div class="quick-stats__info">
                        <h2 class="count">-</h2>
                        <small>Makan-Aja</small>
                    </div>

                    <div class="quick-stats__chart sparkline-bar-stats history"></div>
                </a>
            </div>

            <div class="col-md-3">
                <a href="{{ url('manage/market-aja') }}" class="quick-stats__item bg-cyan quick-overview"
                   data-chart="bar" data-model="storeOrder">
                    <div class="quick-stats__info">
                        <h2 class="count">-</h2>
                        <small>Market-Aja</small>
                    </div>

                    <div class="quick-stats__chart sparkline-bar-stats history"></div>
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-2">Driver Total Orders</h4>
                        <canvas id="driver-orders"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-2">Driver Total Revenue</h4>
                        <canvas id="driver-revenue"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <h4 class="card-title mb-2">Delivery & Service Fees Revenue</h4>
                            </div>
                            <div class="col text-right">
                                <h4 class="card-title mb-2">Rp. <span id="revenue">0</span></h4>
                            </div>
                        </div>
                        <div style="height: 420px; width: 100%">
                            <canvas id="delivery-fees-revenue"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        #dashboard-content .card-body {
            min-height: initial;
            height: initial;
            overflow: auto;
        }

        .quick-stats__info > h2 {
            font-size: 2rem;
        }

        .google-trends-container {
            height: 422px !important;
            min-height: initial !important;
            overflow: hidden !important;
        }
    </style>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let start = moment();
        let end = moment();

        $(document).ready(function () {
            Dashboard.index();
            Dashboard.initRangePicker(function (start, end) {
                $('#reportrange span').html(start.format('D MMMM YYYY') + ' - ' + end.format('D MMMM YYYY'));
                Dashboard.renderDriverChart(start, end);
                Dashboard.renderDailyRevenueChart(start, end);
            });
            Dashboard.renderDriverChart(start, end);
            Dashboard.renderDailyRevenueChart(start, end);
        });
    </script>
@endsection
