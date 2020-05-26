<?php

namespace App\Http\Controllers;

use App\Services\Api2Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CustomersController extends Controller
{
    private $api2cart;


    public function __construct(Api2Cart $api2Cart)
    {
        $this->api2cart = $api2Cart;
    }

    public function index()
    {
        return view('customers.index');
    }

    public function customerList($store_id=null,Request $request)
    {
        \Debugbar::disable();


        /**
         * get account carts & extract exact store info
         */
        $carts = collect($this->api2cart->getCartList());
        $storeInfo = $carts->where('store_key', $store_id)->first();

        $sort_by      = ($request->get('sort_by')) ? $request->get('sort_by') : null;
        $sort_direct  = ($request->get('sort_direct')) ? true : false;
        $created_from = ($request->get('created_from')) ? $request->get('created_from') : null;
        $limit        = ($request->get('limit')) ? $request->get('limit') : null;

//        Log::debug( print_r($request->all(),1) );

        $totalCustomers = $this->api2cart->getCustomerCount( $store_id );

        $customers = collect([]);

        if ( $totalCustomers ){

            $result = $this->api2cart->getCustomerList( $store_id );

            $newRes= (isset($result['result']['customers_count'])) ? collect( $result['result']['customer'] ) : collect([]);
            // put additional information
            if ( $newRes->count() ){
                foreach ($newRes as $item){
                    $newItem = $item;
                    $newItem['cart_id'] = $storeInfo['cart_id'];
                    $customers->push( $newItem );
                }
            }

            if ( isset($result['pagination']['next']) && strlen($result['pagination']['next']) ){
                // get next iteration to load rest customers
                while( isset($result['pagination']['next']) && strlen($result['pagination']['next']) ){
                    $result = $this->api2cart->getCustomerListPage( $store_id , $result['pagination']['next']);
                    $newRes = (isset($result['result']['customers_count'])) ? collect( $result['result']['customer'] ) : collect([]);
                    // put additional information
                    if ( $newRes->count() ){
                        foreach ($newRes as $item){
                            $newItem = $item;
                            $newItem['cart_id'] = $storeInfo['cart_id'];
                            $customers->push( $newItem );
                        }
                    }
                }

            }


        }




        $data = [
            "recordsTotal"      => $totalCustomers,
            "recordsFiltered"   => $totalCustomers,
            "start"             => 0,
            "length"            => 10,
            "data"              => $customers->toArray(),

            'log'               => $this->api2cart->getLog(),
        ];

        return response()->json($data);



    }


    public function subscriberList($store_id=null,Request $request)
    {
        \Debugbar::disable();


        /**
         * get account carts & extract exact store info
         */
        $carts = collect($this->api2cart->getCartList());
        $storeInfo = $carts->where('store_key', $store_id)->first();

//        Log::debug( $store_id );
        $result = $this->api2cart->getSubscriberList( $store_id );



        $data = [
            "recordsTotal"      => 0,
            "recordsFiltered"   => 0,
            "start"             => 0,
            "length"            => 10,
            "data"              => [],

            'log'               => $this->api2cart->getLog(),
        ];

        return response()->json($data);

    }

}
