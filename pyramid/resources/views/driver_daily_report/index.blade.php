@extends('layouts.blank')

@section('title', 'Laporan Harian')

@section('page_actions')
    <button id="reportrange" class="btn btn-outline-success px-3"><i class="zmdi zmdi-calendar"></i>
        <span class="ml-2"></span>
    </button>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-2">Ranking Orderan</h4>
                    <canvas id="driver-orders" style="max-height: 512px;"></canvas>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Orderan Hari Ini</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0 table-hover" id="driver-income">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nama Driver</th>
                                    <th>Total Orderan</th>
                                    <th>Pendapatan</th>
                                    <th>Jumlah Setoran</th>
                                    <th>Jumlah Piutang</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        .jq-stars {
            display: inline-block;
        }

        .jq-rating-label {
            font-size: 22px;
            display: inline-block;
            position: relative;
            vertical-align: top;
            font-family: helvetica, arial, verdana;
        }

        .jq-star {
            width: 100px;
            height: 100px;
            display: inline-block;
            cursor: pointer;
        }

        .jq-star-svg {
            padding-left: 3px;
            width: 100%;
            height: 100% ;
        }

        .jq-star:hover .fs-star-svg path {
        }

        .jq-star-svg path {
            /* stroke: #000; */
            stroke-linejoin: round;
        }

        /* un-used */
        .jq-shadow {
            -webkit-filter: drop-shadow( -2px -2px 2px #888 );
            filter: drop-shadow( -2px -2px 2px #888 );
        }
    </style>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="{{ asset('js/vendor/jquery.star-rating.min.js') }}"></script>
    <script>
        let start = moment();
        let end = moment();

        $(document).ready(function () {
            Dashboard.initRangePicker(function (start, end) {
                $('#reportrange span').html(start.format('D MMMM YYYY') + ' - ' + end.format('D MMMM YYYY'));
                Dashboard.renderDriverIncome(start, end);
                Dashboard.renderDriverChart(start, end);
            });

            Dashboard.renderDriverIncome(start, end);
            Dashboard.renderDriverChart(start, end);

            $(document).on('click', '.order-details', function(e) {
                e.preventDefault();

               let data = JSON.parse($(this).parent().find('.order-data').val());

                let modal = Utils.loadModal('md', true);
                let title = modal.find('.modal-title');
                let body = modal.find('.modal-body');

                title.text('Detail Orderan');
                body.html(`<div class="listview listview--hover"></div>`);
                body.addClass('px-0');

                let listview = body.find('.listview');
                let numberFormatter = new Intl.NumberFormat('id-ID', {
                    style: 'decimal',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                });

                data.forEach((item) => {
                    listview.append(`
                        <div class="listview__item">
                            <div class="listview__content">
                                <div class="listview__heading">
                                    <div class="row">
                                        <div class="col">${item.order_type}</div>
                                        <div class="col text-right">
                                            <h5 class="text-success">+${numberFormatter.format(item.driver_income)}</h5>
                                        </div>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center;">Rating: ${!isNaN(item.rating) ? '<div class="my-rating" data-rating="'+item.rating+'"></div>' : '-'}</div>
                                <div>Review: <i>${item.review}</i></div>
                                <div class="listview__attrs">
                                    <span>Customer: ${item.customer}</span>
                                    <span>Jarak: ${item.distance}km</span>
                                </div>
                            </div>
                        </div>
                    `);
                })

                $(".my-rating").starRating({
                    initialRating: 4,
                    strokeWidth: 10,
                    starSize: 22,
                    readOnly: true
                });
            });
        });
    </script>
@endsection
