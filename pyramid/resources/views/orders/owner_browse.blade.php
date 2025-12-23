@extends('layouts.app')

@section('title', ucwords($module))

@section('content')
    <div class="tab-container">
        <div class="card mb-4">
            <div class="card-body p-0">
                <ul class="nav nav-tabs nav-fill" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#active-orders" role="tab">Active</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#completed-orders" role="tab">Completed</a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="tab-content pt-0">
            <div class="tab-pane active fade show" id="active-orders" role="tabpanel">
            </div>
            <div class="tab-pane fade" id="completed-orders" role="tabpanel"></div>
            <div class="tab-pane fade" id="canceled-orders" role="tabpanel"></div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            moment.locale('id');
            OwnerMakanAjaOrders.index();
        });
    </script>
@endsection
