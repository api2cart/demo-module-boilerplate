<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
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
        $storeInfo = $this->api2cart->getCart( $store_id );


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
            "data"              => $products->toArray(),

            'log'               => $this->api2cart->getLog(),
        ];

        return response()->json($data);



    }


    /**
     * Show the form for editing the specified resource.
     *
     */
    public function edit($store_id=null, $product_id=null, Request $request)
    {
        \Debugbar::disable();

        $product = $this->api2cart->getProductInfo($store_id, $product_id);

        if ( $request->ajax() ){
            return response()->json(['data' => view('products.form',compact('product','store_id', 'product_id'))->render(), 'item' => $product,'log' => $this->api2cart->getLog() ]);
        }

        return redirect(route('products.index'));
    }

    public function update($store_id=null, $product_id=null, ProductRequest $request)
    {
        \Debugbar::disable();

//        Log::debug( "{$store_id} {$product_id}" );
//        Log::debug( $request->all() );

//        $product = $this->api2cart->getProduct( $store_id, $product_id);

        $result = $this->api2cart->updateProduct( $store_id, $product_id, $request->all() );

        if ( $request->ajax() ){

            $storeInfo = $this->api2cart->getCart( $store_id );
            $result['currency'] = ( isset($storeInfo['stores_info'][0]['currency']) ) ? $storeInfo['stores_info'][0]['currency']['iso3'] : '';

            return response()->json(['item' => $result, 'log' => $this->api2cart->getLog()]);
        }

    }



    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy($store_id=null, $product_id=null)
    {
        if ( $this->api2cart->deleteProduct($store_id , $product_id ) ){
            return response()->json([ 'log' => $this->api2cart->getLog() ]);
        } else {
            return response()->json([ 'log' => $this->api2cart->getLog() ], 404);
        }

    }

    public function destroyImage($store_id=null, $product_id=null, Request $request)
    {

//        Log::debug("{$store_id} {$product_id}");
//        Log::debug( $request->all() );

        // use image url as key, cause different store have different info
//        $this->api2cart->deleteProductImage($store_id, $product_id, $request->get('key') );

    }

}
