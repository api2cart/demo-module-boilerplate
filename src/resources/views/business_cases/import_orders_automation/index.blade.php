@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            @include('parts.sidebar')

            <div class="col-lg-10">

                <div class="card">
                    <div class="card-header">Orders <span class="ajax_status"></span></div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <h1>Import order automation workflow</h1>
                                <p>Example: import customer orders from multiple stores and marketplaces into your platform</p>
                                <p>Setting automated order import from e-stores is probably the main challenge of ERP, shipping, warehouse, order and inventory software owners. With API2Cart <a target="_blank" href="https://docs.api2cart.com/order-list">order.list</a> method and webhook for <a target="_blank" href="https://docs.api2cart.com/order-add">order.add</a> event you can do it easily!</p>
                                <p class="text-center"><img class="img-fluid" src="{{ asset('images/import-orders-1.jpg') }}"></p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col text-right api_log">
                                <a href="#" id="showApiLog" >Performed <span>0</span> requests with API2Cart. Click to see details...</a><br>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="dtable" class="table table-bordered" style="width: 100%; font-size: 12px;">
                                <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Date</th>
                                    <th>Store</th>
                                    <th>Customer</th>
                                    <th>Shipping Address</th>
                                    <th>Status</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                            </table>
                        </div>


                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
