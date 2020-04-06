<?php

namespace App\Http\Controllers;

use App\Services\Api2Cart;
use Illuminate\Http\Request;

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

        $carts = $this->api2cart->getCartList();
        $stores = ($carts['result']['carts_count']) ? collect( $carts['result']['carts'] ) : collect([]);
        $storeInfo = $stores->where('store_key', $store_id)->first();

        $totalCustomers = $this->api2cart->getCustomerCount( $store_id )['result']['customers_count'];



        $perPage = 10;
        $totalPages = $totalCustomers / $perPage;
        $currPage   = $request->get('start') ? ($request->get('start')/$perPage)+1 : 1;

        $results = $this->api2cart->getCustomerList( $store_id, 0, 10 );
        $customers = collect([]);

        $newCustomers = ($results['result']['customers_count']) ? collect( $results['result']['customer'] ) : collect([]);

        if ( $newCustomers->count() ){
            foreach ($newCustomers as $item){
                $newItem = $item;
                $newItem['cart_id'] = $storeInfo['cart_id'];
                $customers->push( $newItem );
            }
        }


        if ( isset($results['pagination']['next']) && strlen($results['pagination']['next']) ){
            // get next iteration to load all orders

            while( isset($results['pagination']['next']) && strlen($results['pagination']['next']) ){
                $results = $this->api2cart->getCustomerListPage( $store_id , $results['pagination']['next']);

                $newCustomers = ($results['result']['customers_count']) ? collect( $results['result']['customer'] ) : collect([]);

                if ( $newCustomers->count() ){
                    foreach ($newCustomers as $item){
                        $newItem = $item;
                        $newItem['cart_id'] = $storeInfo['cart_id'];
                        $customers->push( $newItem );
                    }
                }



            }


        }




        $data = [
            "recordsTotal"      => $totalCustomers,
            "recordsFiltered"   => $totalCustomers,
            "start"             => 0,
            "length"            => 10,
            "data"              => $customers->toArray()

        ];

        return response()->json($data);



    }


}
