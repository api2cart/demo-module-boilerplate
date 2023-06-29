<br>
@if( isset($shipments) && count($shipments) )
    @foreach($shipments as $shipment)
        <div class="card" style="background-color: #343434; margin-bottom: 15px">
            <div class="card-body">
                <div class="row">
                    <div class="col-6" style="text-align: left">
                        <div><span>ID:</span> {{ $shipment['id']  }}</div>
                        <div><span>Order ID:</span>{{ $shipment['order_id']  }}</div>
                        <div><span>Warehouse ID:</span>{{ $shipment['warehouse_id'] ?? '' }}</div>
                        <div><span>Created At:</span>{{ $shipment['created_at']['value']  }}</div>
                    </div>
                    <div class="col-6"  style="text-align: left">
                        @if ($shipment['tracking_numbers'])
                            <h5>Tracking Numbers</h5>
                        @endif
                        @foreach( $shipment['tracking_numbers'] as $trackingNumber )
                            <div><span>Carrier:</span> {{ $carriers[$trackingNumber['carrier_id']]['name'] ?? $trackingNumber['carrier_id']  }}</div>
                            <div><span>Tracking Number:</span>"{{ $trackingNumber['tracking_number'] }}"</div><br>
                        @endforeach
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col">
                        <h3 class="text-left">Products:</h3>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th scope="col">Id</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Model</th>
                                    <th scope="col">Price</th>
                                    <th scope="col">Quantity</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach( $shipment['items'] as $item )
                                    <tr>
                                        <th>{{ $item['product_id'] }}</th>
                                        <td>{{ $item['name'] }}</td>
                                        <td>{{ $item['model'] }}</td>
                                        <td>{{ $item['price'] }}</td>
                                        <td>{{ $item['quantity'] }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@else
    <h3>No shipments in this order.</h3>
@endif