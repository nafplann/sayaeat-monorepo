@extends('layouts.app')

@section('title', 'Dashboard')

@section('page_actions')
    <button id="reportrange" class="btn btn-light btn--raised px-3"><i class="zmdi zmdi-calendar"></i>
        <span class="ml-2"></span>
    </button>
@endsection

@section('content')
    <div id="dashboard-content">
        <div class="row">
            <div class="col-md-3">
                <a href="{{ url('manage/customers') }}" class="quick-stats__item bg-green quick-overview"
                   data-chart="bar" data-model="customer">
                    <div class="quick-stats__info">
                        <h2 class="count">-</h2>
                        <small>Total Orders</small>
                    </div>
                </a>
            </div>

            <div class="col-md-3">
                <a href="{{ url('manage/customers') }}" class="quick-stats__item bg-amber quick-overview"
                   data-chart="bar" data-model="customer">
                    <div class="quick-stats__info">
                        <h2 class="count">-</h2>
                        <small>Total Revenue</small>
                    </div>
                </a>
            </div>

            <div class="col-md-3">
                <a href="{{ url('manage/customers') }}" class="quick-stats__item bg-blue quick-overview"
                   data-chart="bar" data-model="customer">
                    <div class="quick-stats__info">
                        <h2 class="count">-</h2>
                        <small>Delivery Fees</small>
                    </div>
                </a>
            </div>

            <div class="col-md-3">
                <a href="{{ url('manage/customers') }}" class="quick-stats__item bg-red quick-overview"
                   data-chart="bar" data-model="customer">
                    <div class="quick-stats__info">
                        <h2 class="count">-</h2>
                        <small>Service Fees</small>
                    </div>
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
    <script>
        $(function () {

            var start = moment().subtract(29, 'days');
            var end = moment();

            function cb(start, end) {
                $('#reportrange span').html(start.format('D MMMM YYYY') + ' - ' + end.format('D MMMM YYYY'));
            }

            $('#reportrange').daterangepicker({
                startDate: start,
                endDate: end,
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                }
            }, cb);

            cb(start, end);

        });
    </script>
@endsection
