<?php

namespace App\Http\Controllers;

use App\Services\Api2Cart;
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
        $storeInfo = $carts->where('store_key', $store_id)->first();

        $totalOrders = $this->api2cart->getOrderCount( $store_id );

        $orders = collect([]);

        if ( $totalOrders ){

            $result = $this->api2cart->getOrderList( $store_id );

            $newOrders = (isset($result['result']['orders_count'])) ? collect( $result['result']['order'] ) : collect([]);
            // put additional information
            if ( $newOrders->count() ){
                foreach ($newOrders as $item){
                    $newItem = $item;
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
                            $newItem['cart_id'] = $storeInfo['cart_id'];
                            $orders->push( $newItem );
                        }
                    }
                }

            }


        }


        $sorted = $orders->sortBy('create_at.value', null, true );
//        Log::debug( print_r($sorted,1) );

        $data = [
            "recordsTotal"      => $totalOrders,
            "recordsFiltered"   => $totalOrders,
            "start"             => 0,
            "length"            => 10,
            "data"              => $sorted->toArray(),

            'log'               => $this->api2cart->getLog(),
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

    public function create(Request $request)
    {
        $carts = collect($this->api2cart->getCartList());

//        Log::debug( print_r($carts,1) );

        if ( $request->ajax() ){
            return response()->json( ['data' => view('orders.form', compact('carts'))->render(), 'item' => $carts ] );
        }
        return redirect( route('orders.index') );
    }

    public function store(Request $request)
    {
//        Log::debug( print_r($request->all(),1) );

        $cart = $this->api2cart->getCart( $request->get('cart_id') );
        $customer = $this->api2cart->getCustomer( $request->get('cart_id'), $request->get('customer_id') );

        $address = collect( $customer['address_book'] );
        $billing = $address->where('type', 'billing')->first();
        $shipping= $address->where('type', 'shipping')->first();

//        Log::debug( print_r( $address->where('type', 'billing')->first(), 1 ) );

        $order = [
            'store_id'          => $cart['stores_info'][0]['store_id'],
            'customer_email'    => $customer['email'],
            'order_status'      => 'Processing',
            'subtotal_price'    => 0,
            'total_price'       => 0,

            'bill_first_name'   => $billing['first_name'],
            'bill_last_name'    => $billing['last_name'],
            'bill_address_1'    => $billing['address1'],
            'bill_city'         => $billing['city'],
            'bill_postcode'     => $billing['postcode'],

            // state & country need be cleared
            'bill_state'        => $billing['state']['code'],
            'bill_country'      => $billing['country']['code3'],



        ];

        foreach ($request->get('checked_id') as $cpi){
            $product  = $this->api2cart->getProductInfo( $request->get('cart_id'), $cpi );
            $quantity = $request->get('product_quantity')[ array_search($cpi, $request->get('product_id')) ];

//            Log::debug( print_r($product,1));

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

        $result = $this->api2cart->createOrder( $request->get('cart_id') , $order );

        if ($result){


            return response()->json([ 'log' => $this->api2cart->getLog(), 'item' => $this->api2cart->getOrderInfo( $request->get('cart_id'), $result['order_id'] ) ]);

        } else {
            // error creating order
            return response()->json([ 'log' => $this->api2cart->getLog() ], 404);
        }


    }

}
