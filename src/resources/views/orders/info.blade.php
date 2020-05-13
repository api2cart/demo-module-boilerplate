<br>
<div class="row">
    <div class="col">
        <h3 class="text-left">Shipment address</h3>

        <div class="row text-left">
            <div class="col">{{ $order['customer']['first_name'] }} {{ $order['customer']['last_name'] }}</div>
        </div>
        <div class="row text-left">
            <div class="col">{{ $order['shipping_address']['address1'] }}</div>
        </div>
        @if(isset($order['shipping_address']['address2']))
            <div class="row text-left">
                <div class="col">{{ $order['shipping_address']['address2'] }}</div>
            </div>
        @endif
        <div class="row text-left">
            <div class="col">{{ $order['shipping_address']['city'] }}, {{ (isset($order['shipping_address']['state']['code'])) ? $order['shipping_address']['state']['code'] : '' }} {{ (isset($order['shipping_address']['postcode'])) ? $order['shipping_address']['postcode'] : ''}}</div>
        </div>
        <div class="row text-left">
            <div class="col">{{ (isset($order['shipping_address']['country']['code3'])) ? $order['shipping_address']['country']['code3'] : '' }}</div>
        </div>

    </div>

    <div class="col">
        <h3 class="text-left">Billing address</h3>

        <div class="row text-left">
            <div class="col">{{ $order['customer']['first_name'] }} {{ $order['customer']['last_name'] }}</div>
        </div>
        <div class="row text-left">
            <div class="col">{{ $order['billing_address']['address1'] }}</div>
        </div>
        @if(isset($order['billing_address']['address2']))
            <div class="row text-left">
                <div class="col">{{ $order['billing_address']['address2'] }}</div>
            </div>
        @endif
        <div class="row text-left">
            <div class="col">{{ $order['billing_address']['city'] }}, {{ (isset($order['billing_address']['state']['code'])) ? $order['billing_address']['state']['code'] : '' }} {{ (isset($order['billing_address']['postcode'])) ? $order['billing_address']['postcode'] : ''}}</div>
        </div>
        <div class="row text-left">
            <div class="col">{{ (isset($order['billing_address']['country']['code3'])) ? $order['billing_address']['country']['code3'] : '' }}</div>
        </div>

    </div>

</div>

<div class="row">
    <div class="col"><br></div>
</div>

<div class="row">
    <div class="col">
        <h3 class="text-left">Shipping method</h3>
        <div class="row text-left">
            <div class="col">{{ (isset($order['shipping_method']['name'])) ? $order['shipping_method']['name'] : '' }}</div>
        </div>
    </div>
</div>

