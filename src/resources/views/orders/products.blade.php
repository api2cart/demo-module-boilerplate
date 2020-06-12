<br>
@if( isset($order['order_products']) && count($order['order_products']) )
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
                        <th scope="col">Total</th>
                    </tr>
                    </thead>
                    <tbody>
                        @foreach( $order['order_products'] as $item )
                            <tr>
                                <th>{{ $item['product_id'] }}</th>
                                <td>{{ $item['name'] }}</td>
                                <td>{{ $item['model'] }}</td>
                                <td>{{ $item['price'] }}</td>
                                <td>{{ $item['quantity'] }}</td>
                                <td>{{ $item['total_price'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>


        </div>



    </div>
@else
    <h3>No products in this order.</h3>
@endif


