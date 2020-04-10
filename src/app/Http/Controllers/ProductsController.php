<?php

namespace App\Http\Controllers;

use App\Services\Api2Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductsController extends Controller
{
    private $api2cart;

    public function __construct(Api2Cart $api2Cart)
    {
        $this->api2cart = $api2Cart;
    }

    public function index()
    {
        return view('products.index');
    }

    public function productList($store_id=null,Request $request)
    {
        \Debugbar::disable();



        /**
         * get account carts & extract exact store info
         */
        $carts = collect($this->api2cart->getCartList());
        $storeInfo = $this->api2cart->getCart( $store_id );;


        $totalProducts = $this->api2cart->getProductCount( $store_id );

        $products = collect([]);

        if ( $totalProducts ){

            $result = $this->api2cart->getProductList( $store_id );

            $newRes= (isset($result['result']['products_count'])) ? collect( $result['result']['product'] ) : collect([]);
            // put additional information
            if ( $newRes->count() ){
                foreach ($newRes as $item){
                    $newItem = $item;
                    $newItem['currency'] = ( isset($storeInfo['stores_info'][0]['currency']) ) ? $storeInfo['stores_info'][0]['currency']['iso3'] : '';
                    $products->push( $newItem );
                }
            }


            if ( isset($result['pagination']['next']) && strlen($result['pagination']['next']) ){
                // get next iteration to load rest customers
                while( isset($result['pagination']['next']) && strlen($result['pagination']['next']) ){
                    $result = $this->api2cart->getProductListPage( $store_id , $result['pagination']['next']);
                    $newRes = (isset($result['result']['products_count'])) ? collect( $result['result']['product'] ) : collect([]);
                    // put additional information
                    if ( $newRes->count() ){
                        foreach ($newRes as $item){
                            $newItem = $item;
                            $newItem['currency'] = ( isset($storeInfo['stores_info'][0]['currency']) ) ? $storeInfo['stores_info'][0]['currency']['iso3'] : '';
                            $products->push( $newItem );
                        }
                    }
                }

            }


        }


        $data = [
            "recordsTotal"      => $totalProducts,
            "recordsFiltered"   => $totalProducts,
            "start"             => 0,
            "length"            => 10,
            "data"              => $products->toArray()

        ];

        return response()->json($data);



    }

}
