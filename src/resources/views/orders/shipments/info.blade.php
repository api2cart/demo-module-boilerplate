<br>
@if( isset($shipments) && count($shipments) )
    @foreach($shipments as $shipment)
        <form method="POST" id="shimpent-{{$shipment['id']}}" action="{{route('order.shipment.update')}}">
            @csrf
            <div class="card" style="background-color: #343434; margin-bottom: 15px">
                <div class="card-body">
                    <div class="alert" id="alert-{{$shipment['id']}}" role="alert" style="display: none;">
                        <div id="_form_errors" class="text-left"></div>
                    </div>
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
                            @foreach( $shipment['tracking_numbers'] as $key => $trackingNumber )
                                <div class="form-group row">
                                    <label for="carrier_id" class="col-4 col-form-label">Carrier:</label>
                                    <div class="col-8">
                                        @if(!empty($carriers))
                                            <select class="custom-select" id="carrier_id" name="tracking_numbers[{{ $key }}][carrier_id]" required>
                                                @foreach($carriers as $carrier)
                                                    <option value="{{ $carrier['id'] }}" {{ $trackingNumber['carrier_id'] === $carrier['id'] ? 'selected' : '' }}>{{ $carrier['name'] }}</option>
                                                @endforeach
                                            </select>
                                        @else
                                            <input type="text" class="custom-select" id="carrier_id" name="tracking_numbers[{{ $key }}][carrier_id]" value="{{$trackingNumber['carrier_id']}}" required>
                                        @endif
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="tracking_number" class="col-4 col-form-label">Tracking Number</label>
                                    <div class="col-8">
                                        <input type="text" class="form-control" id="tracking_number" name="tracking_numbers[{{ $key }}][tracking_number]" value="{{ $trackingNumber['tracking_number'] }}">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-primary" onclick="updateShipment(this, '{{$shipment['id']}}', {{$key}})">Update Tracking Number</button>
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
            <input type="hidden" name="shipment_id" value="{{$shipment['id']}}">
            <input type="hidden" name="order_id" value="{{$orderId}}">
            <input type="hidden" name="store_key" value="{{$storeKey}}">
        </form>
    @endforeach
@else
    <h3>No shipments in this order.</h3>
@endif