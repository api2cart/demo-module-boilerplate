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




        $data = [
            "recordsTotal"      => $totalOrders,
            "recordsFiltered"   => $totalOrders,
            "start"             => 0,
            "length"            => 10,
            "data"              => $orders->toArray(),

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

}
