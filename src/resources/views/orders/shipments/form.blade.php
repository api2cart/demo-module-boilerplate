{!! Form::open(['url' => route('order.shipment.store') ]) !!}

<br>
<div class="row text-left">
    <div class="col">

        <div class="alert alert-danger" role="alert" style="display: none;">
            <div id="_form_errors" class="text-left"></div>
        </div>

        <div class="form-group row">
            <label for="carrier_id" class="col-2 col-form-label">Carrier:</label>
            <div class="col-10">
                @if(!empty($carriers))
                <select class="custom-select" id="carrier_id" name="carrier_id" required>
                    @foreach($carriers as $carrier)
                        <option value="{{ $carrier['id'] }}">{{ $carrier['name'] }}</option>
                    @endforeach
                </select>
                @else
                    <input type="text" class="custom-select" id="carrier_id" name="carrier_id" required>
                @endif
                <div class="invalid-feedback"></div>
            </div>
        </div>

        <div class="form-group row">
            <label for="tracking_number" class="col-2 col-form-label">Tracking Number</label>
            <div class="col-10">
                <input type="text" class="form-control" id="tracking_number" name="tracking_number">
                <div class="invalid-feedback"></div>
            </div>
        </div>

        <div class="form-group row">
            <label for="all_products" class="col-2 col-form-label">All Products</label>
            <div class="col-10" style="padding-top: 7.5px">
                <input type="checkbox" class="custom-checkbox" id="all_products" onchange="disableProducts(this, event)" name="all_products">
                <div class="invalid-feedback"></div>
            </div>
        </div>
        <div class="form-group row" id="product_groups">
            <label for="" class="col-2 col-form-label">Products</label>
            <div class="col-10" >
                <table class="table">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Ordered Qty</th>
                        <th>Shipped Qty</th>
                        <th>Avail Qty</th>
                    </tr>
                    </thead>
                    <tbody>
                        @foreach($itemsToShip as $item)
                            <tr>
                                <td>
                                    {{$item['name']}}
                                </td>
                                <td>
                                    {{$item['order_quantity']}}
                                </td>
                                <td>
                                    {{$item['shipped_quantity']}}
                                </td>
                                <td>
                                    @if ($item['quantity'] == 0)
                                        <input type="number" name="items[{{ $item['order_product_id'] }}][quantity]" class="form-control" placeholder="Quantity" value="0" disabled>
                                    @else
                                        <input type="number" name="items[{{ $item['order_product_id'] }}][quantity]" class="form-control" placeholder="Quantity" value="{{$item['quantity']}}" min="0" max="{{$item['quantity']}}">
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <input type="hidden" name="order_id" value="{{$orderId}}">
        <input type="hidden" name="store_key" value="{{$storeKey}}">

    </div>
</div>


{!! Form::close() !!}