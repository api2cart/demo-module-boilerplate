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

        $carts = $this->api2cart->getCartList();
        $stores = ($carts['result']['carts_count']) ? collect( $carts['result']['carts'] ) : collect([]);
        $storeInfo = $stores->where('store_key', $store_id)->first();

        $totalOrders = $this->api2cart->getOrderCount( $store_id )['result']['orders_count'];

        $perPage = 10;
        $totalPages = $totalOrders / $perPage;
        $currPage   = $request->get('start') ? ($request->get('start')/$perPage)+1 : 1;

        $results = $this->api2cart->getOrderList( $store_id, 0, 10 );
        $orders = collect([]);

        $newOrders = ($results['result']['orders_count']) ? collect( $results['result']['order'] ) : collect([]);

        if ( $newOrders->count() ){
            foreach ($newOrders as $item){
                $newItem = $item;
                $newItem['cart_id'] = $storeInfo['cart_id'];
                $orders->push( $newItem );
            }
        }



        if ( isset($results['pagination']['next']) && strlen($results['pagination']['next']) ){
            // get next iteration to load all orders

            while( isset($results['pagination']['next']) && strlen($results['pagination']['next']) ){
                $results = $this->api2cart->getOrderListPage( $store_id , $results['pagination']['next']);

                $newOrders = ($results['result']['orders_count']) ? collect( $results['result']['order'] ) : collect([]);

                if ( $newOrders->count() ){
                    foreach ($newOrders as $item){

                        $newItem = $item;
                        $newItem['cart_id'] = $storeInfo['cart_id'];

                        $orders->push( $newItem );
                    }
                }


            }


        }



//        Log::debug( print_r($results,1) );




        $data = [
            "recordsTotal"      => $totalOrders,
            "recordsFiltered"   => $totalOrders,
            "start"             => 0,
            "length"            => 10,
            "data"              => $orders->toArray()

        ];

        return response()->json($data);

    }

}
