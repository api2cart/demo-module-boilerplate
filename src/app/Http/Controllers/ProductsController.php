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

        $allCarts = collect( $this->api2cart->getCartsList()['result']['supported_carts'] );

        $carts = $this->api2cart->getCartList();
        $stores = ($carts['result']['carts_count']) ? collect( $carts['result']['carts'] ) : collect([]);

        $storeInfo      = $stores->where('store_key', $store_id)->first();
        $info           = $this->api2cart->getCart( $store_id )['result']['stores_info'][0];

        $totalProducts = $this->api2cart->getProductCount( $store_id )['result']['products_count'];



        $perPage = 10;
        $totalPages = $totalProducts / $perPage;
        $currPage   = $request->get('start') ? ($request->get('start')/$perPage)+1 : 1;

        $results = $this->api2cart->getProductList( $store_id, 0, 10 );
        $products = collect([]);

        $newProducts = ($results['result']['products_count']) ? collect( $results['result']['product'] ) : collect([]);

        if ( $newProducts->count() ){
            foreach ($newProducts as $item){
                $newItem = $item;

                $newItem['stores_info']   = $info;
                $newItem['cart_info']     = $allCarts->where('cart_id', $storeInfo['cart_id'])->first();

                $products->push( $newItem );
            }
        }


        if ( isset($results['pagination']['next']) && strlen($results['pagination']['next']) ){
            // get next iteration to load all orders

            while( isset($results['pagination']['next']) && strlen($results['pagination']['next']) ){
                $results = $this->api2cart->getProductListPage( $store_id , $results['pagination']['next']);

                $newProducts = ($results['result']['products_count']) ? collect( $results['result']['product'] ) : collect([]);

                if ( $newProducts->count() ){
                    foreach ($newProducts as $item){
                        $newItem = $item;
                        $newItem['stores_info']   = $info;
                        $newItem['cart_info']     = $allCarts->where('cart_id', $storeInfo['cart_id'])->first();
                        $products->push( $newItem );
                    }
                }



            }


        }

//        Log::debug( print_r($products->forPage(1,2),1) );


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
