@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div id="dashboard-content">
        <div class="row">
            <div class="col-sm-6 col-md-3">
                <a href="{{ url('manage/users') }}" class="quick-stats__item bg-green quick-overview" data-chart="bar" data-model="user1">
                    <div class="quick-stats__info">
                        <h2 class="count">-</h2>
                        <small>Worldwide Users</small>
                    </div>

                    <div class="quick-stats__chart sparkline-bar-stats history"></div>
                </a>
            </div>

            <div class="col-sm-6 col-md-3">
                <a href="{{ url('manage/applications') }}" class="quick-stats__item bg-amber quick-overview" data-chart="bar" data-model="application1">
                    <div class="quick-stats__info">
                        <h2 class="count">-</h2>
                        <small>Applications</small>
                    </div>

                    <div class="quick-stats__chart sparkline-bar-stats history"></div>
                </a>
            </div>

            <div class="col-sm-6 col-md-3">
                <a href="{{ url('manage/products') }}" class="quick-stats__item bg-blue quick-overview" data-chart="line" data-model="product1">
                    <div class="quick-stats__info">
                        <h2 class="count">-</h2>
                        <small>Products</small>
                    </div>
                    <div class="quick-stats__chart sparkline-bar-stats history"></div>
                </a>
            </div>

            <div class="col-sm-6 col-md-3">
                <a href="{{ url('manage/orders') }}" class="quick-stats__item bg-red quick-overview" data-chart="line" data-model="order1">
                    <div class="quick-stats__info">
                        <h2 class="count">-</h2>
                        <small>Orders</small>
                    </div>
                    <div class="quick-stats__chart sparkline-bar-stats history"></div>
                </a>
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
    <script src="https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB6rQ_NRxnsrdbyTNmu-FtY1tfClsCPzsg" defer></script>
    <script>
        $(document).ready(function() {
            Dashboard.index();
        });
    </script>
@endsection
