<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\Services\Api2Cart;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrdersController extends Controller
{

    private $api2cart;


    public function __construct(Api2Cart $api2Cart)
    {
        $this->api2cart = $api2Cart;
    }

    public function index()
    {
        return view('orders.index');
    }

    public function orderList($store_id=null,Request $request)
    {
        \Debugbar::disable();

        /**
         * get account carts & extract exact store info
         */
        $carts = collect($this->api2cart->getCartList());

        $totalOrders = 0;
        $orders = collect([]);
        $logs = collect([]);

        $sort_by      = ($request->get('sort_by')) ? $request->get('sort_by') : null;
        $sort_direct  = ($request->get('sort_direct')) ? true : false;
        $created_from = ($request->get('created_from')) ? Carbon::parse($request->get('created_from'))->format("Y-m-d\TH:i:sO") : null;
        $limit        = ($request->get('limit')) ? (string)$request->get('limit') : null;
        $length       = ($request->get('length')) ? $request->get('length') : 15;
        $storeKeys    = ($request->get('storeKeys')) ?: ($store_id !== null ? [$store_id] : []);


        foreach ($storeKeys as $store_id) {
            $storeInfo = $carts->where('store_key', $store_id)->first();
            $totalOrders = $this->api2cart->getOrderCount( $store_id );

            if ( $totalOrders ) {

                $result = $this->api2cart->getOrderList(
                    $store_id,
                    $sort_by,
                    null,
                    $limit,
                    $created_from
            );

                $newOrders = (isset($result['result']['orders_count'])) ? collect( $result['result']['order'] ) : collect([]);

                // put additional information
                if ( $newOrders->count() ){
                    foreach ($newOrders as $item){
                        $newItem = $item;
                        $newItem['create_at']['value'] = Carbon::parse($item['create_at']['value'])->setTimezone('UTC')->format("Y-m-d\TH:i:sO");
                        $newItem['cart_id'] = $storeInfo['cart_id'];
                        $orders->push( $newItem );
                    }
                }


                if ( isset($result['pagination']['next']) && strlen($result['pagination']['next']) ){
                    // get next iteration to load rest orders
                    while( isset($result['pagination']['next']) && strlen($result['pagination']['next']) ){
                        $result = $this->api2cart->getOrderListPage( $store_id , $result['pagination']['next']);
                        $newOrders = (isset($result['result']['orders_count'])) ? collect( $result['result']['order'] ) : collect([]);

                        // put additional information
                        if ( $newOrders->count() ){
                            foreach ($newOrders as $item){
                                $newItem = $item;
                                $newItem['create_at']['value'] = Carbon::parse($item['create_at']['value'])->setTimezone('UTC')->format("Y-m-d\TH:i:sO");
                                $newItem['cart_id'] = $storeInfo['cart_id'];
                                $orders->push( $newItem );
                            }
                        }
                    }

                }

                foreach ($this->api2cart->getLog()->all() as $item) {
                    $logs->push($item);
                }
            }
        }

        if ( $sort_by  ){
            switch ($sort_by){
                case 'create_at':
                    $sort_by = 'create_at.value';
                    break;
                default:
                    $sort_by = 'create_at.value';
                    break;
            }
            $sorted = $orders->sortBy($sort_by, null, $sort_direct );
        } else {
            $sorted = $orders->sortBy('create_at.value', null, $sort_direct );
        }

        $data = [
            "recordsTotal"      => $totalOrders,
            "recordsFiltered"   => $totalOrders,
            "start"             => 0,
            "length"            => $length,
            "data"              => ($length) ? $sorted->forPage(0, $length) : $sorted->toArray(),
            'log'               => $logs,
        ];

        return response()->json($data);
    }


    public function orderInfo($store_id=null,$order_id=null,Request $request)
    {
        $order = $this->api2cart->getOrderInfo( $store_id, $order_id );

        if ( $request->ajax() ){
            return response()->json(['data' => view('orders.info',compact('order','store_id', 'order_id'))->render(), 'item' => $order,'log' => $this->api2cart->getLog() ]);
        }

        return redirect( route('orders.index') );
    }

    public function orderProducts($store_id=null,$order_id=null,Request $request)
    {
        $order = $this->api2cart->getOrderInfo( $store_id, $order_id );

        /**
         * get all product's id to featch from 1 request insteed 1 per product
         *

        $pids = [];
        foreach ( $order['order_products'] as $item ){
            $pids[] = $item['product_id'];
        }
        $products = [];
        if (count($pids)){
            $products = $this->api2cart->getProductList( $store_id, $pids );
        }

        */


        if ( $request->ajax() ){
            return response()->json(['data' => view('orders.products',compact('order','store_id', 'order_id'))->render(), 'item' => $order,'log' => $this->api2cart->getLog() ]);
        }

        return redirect( route('orders.index') );
    }

    public function abandoned($store_id=null, Request $request)
    {
        $data = [
            "recordsTotal" => 0,
            "recordsFiltered" => 0,
            "start" => 0,
            "length" => 10,
            "data" => collect([]),
            'log' => $this->api2cart->getLog(),
        ];

        if ($store_id) {
            $items = collect($this->api2cart->getAbandonedCart($store_id));
            $data = [
                "recordsTotal" => (is_array($items)) ? count($items) : 0,
                "recordsFiltered" => (is_array($items)) ? count($items) : 0,
                "start" => 0,
                "length" => 10,
                "data" => collect($items),
                'log' => $this->api2cart->getLog(),
            ];
        } elseif ($storeIds = $request->get('storeKeys', [])) {
            $orders = collect([]);

            foreach ($storeIds as $store_id) {
                $items = collect($this->api2cart->getAbandonedCart($store_id));

                if ($items->count()) {
                    foreach ($items as $item) {
                        $newItem = $item;
                        $newItem['cart_id'] = $store_id;
                        $newItem['created_at']['value'] = Carbon::parse($item['created_at']['value'])->setTimezone('UTC')->format("Y-m-d\TH:i:sO");
                        $orders->push($newItem);
                    }
                }
            }

            $data = [
                "recordsTotal" => $orders->count(),
                "recordsFiltered" => $orders->count(),
                "start" => 0,
                "length" => 10,
                "data" => $orders->sortBy('created_at.value', null, true)->toArray(),
                'log' => $this->api2cart->getLog(),
            ];
        }

        return response()->json($data);
    }

    public function statuses($store_id=null, Request $request)
    {
        $statuses = $this->api2cart->getOrderStatuses( $store_id );

        if ( !$statuses ){
            return response()->json(['log' => $this->api2cart->getLog() ], 404);
        }

        if ( $request->ajax() ){
            return response()->json(['data' => collect($statuses['cart_order_statuses']), 'log' => $this->api2cart->getLog() ]);
        }

        return redirect( route('orders.index') );
    }

    public function create(Request $request)
    {
        $carts = collect($this->api2cart->getCartList());

        if ( $request->ajax() ){
            return response()->json( ['data' => view('orders.form', compact('carts'))->render(), 'item' => $carts ] );
        }

        return redirect( route('orders.index') );
    }

    public function store(OrderRequest $request)
    {
        $cart = $this->api2cart->getCart( $request->get('cart_id') );
        $customer = $this->api2cart->getCustomer( $request->get('cart_id'), $request->get('customer_id') );

        $address = collect( $customer['address_book'] );
        $billing = $address->where('type', 'billing')->first();
        $shipping= $address->where('type', 'shipping')->first();

        // for any case if only shipping avialable
        if ($billing == null) $billing = $shipping;

        // some customers do not have state
        if ( !isset($shipping['state']['code']) || $shipping['state']['code'] == '' ) $shipping['state']['code'] = 'AL';

        $order = [
            'store_id'          => $cart['stores_info'][0]['store_id'],
            'customer_email'    => $customer['email'],
            'order_status'      => $request->get('status_id'),
            'subtotal_price'    => 0,
            'total_price'       => 0,

            'bill_first_name'   => $billing['first_name'] ?: $billing['first_name'] ?: $shipping['first_name'] ?: 'John',
            'bill_last_name'    => $billing['last_name'] ?: $billing['last_name'] ?: $shipping['last_name'] ?: 'Doe',
            'bill_address_1'    => $billing['address1'] ?: $billing['address1'] ?: $shipping['address1'] ?: '221b, Baker street',
            'bill_city'         => $billing['city'] ?: $billing['city'] ?: $shipping['city'] ?: 'London',
            'bill_postcode'     => $billing['postcode'] ?: $billing['postcode'] ?: $shipping['postcode'] ?: '00000',

            // state & country need be cleared
            'bill_state'        => (isset($billing['state']['code']) && $billing['state']['code'] != '') ? $billing['state']['code'] : $shipping['state']['code'],
            'bill_country'      => $billing['country']['code3'] ?: $billing['country']['code3'] ?: $shipping['country']['code3'] ?: 'UK',
        ];

        foreach ($request->get('checked_id') as $cpi){
            $product  = $this->api2cart->getProductInfo( $request->get('cart_id'), $cpi );
            $quantity = $request->get('product_quantity')[ array_search($cpi, $request->get('product_id')) ];

            // check if quantity right
            if ( $product['quantity']< $quantity) continue;

            $order['order_item'][] = [
                'order_item_id'         => $product['id'],
                'order_item_name'       => $product['name'],
                'order_item_model'      => $product['u_model'],
                'order_item_price'      => $product['price'],
                'order_item_quantity'   => $quantity
            ];

            $order['subtotal_price']    += $product['price'] * $quantity;
            $order['total_price']       += $product['price'] * $quantity;
        }

        list($returnCode, $result) = $this->api2cart->createOrder( $request->get('cart_id') , $order );

        if ($returnCode == 0){
            return response()->json([ 'log' => $this->api2cart->getLog(), 'success' => true, 'item' => $this->api2cart->getOrderInfo( $request->get('cart_id'), $result['order_id'] ) ]);
        } else {
            // error creating order
            return response()->json([ 'log' => $this->api2cart->getLog(), 'success' => false, 'errormessage' => $result ]);
        }

    }

}
