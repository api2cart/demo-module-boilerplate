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

        $sort_by      = ($request->get('sort_by')) ? $request->get('sort_by') : null;
        $sort_direct  = ($request->get('sort_direct')) ? true : false;
        $created_from = ($request->get('created_from')) ? $request->get('created_from') : null;
        $limit        = ($request->get('limit')) ? $request->get('limit') : null;

//        Log::debug( print_r($storeInfo,1) );

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

                    // collect product variants
                    if ( $item['type'] === 'configurable' ){
                        $pv = $this->api2cart->getProductVariants($store_id, $item['id'] );
                        $newItem['children'] = $pv['children'];
                    }

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

                            // collect product variants
                            if ( $item['type'] === 'configurable' ){
                                $pv = $this->api2cart->getProductVariants($store_id, $item['id'] );
                                $newItem['children'] = $pv['children'];
                            }

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

        if ( $product['type'] === 'configurable' ){
            $pv = $this->api2cart->getProductVariants($store_id, $product['id'] );
            $product['children'] = $pv['children'];
        }

//        Log::debug( 'edit product ');
//        Log::debug( print_r($product,1));

        if ( $request->ajax() ){
            return response()->json(['data' => view('products.form',compact('product','store_id', 'product_id'))->render(), 'item' => $product,'log' => $this->api2cart->getLog() ]);
        }

        return redirect(route('products.index'));
    }

    public function update($store_id=null, $product_id=null, ProductRequest $request)
    {
        \Debugbar::disable();

//        Log::debug( $request->all() );


        $product = $this->api2cart->getProductInfo( $store_id, $product_id);
        $diff = array_diff_assoc_recursive( $request->except(['_token','allSelectedProducts','selected_items','images']), $product );

        if ( $product['type'] === 'configurable' ){
            $diff['price'] = null;
        }

//        Log::debug( print_r($diff,1) );

        if ( $request->get('allSelectedProducts') ){

            foreach ($request->get('selected_items') as $item){
                $pid = explode(':', $item);

//                Log::debug( print_r($pid,1) );

                if ( isset($pid[0]) && isset($pid[1]) && $pid[0] && $pid[1] ){

                    $storeInfo = $this->api2cart->getCart( $store_id );
                    $res = $this->api2cart->updateProduct( $pid[0], $pid[1], $diff );
                    $res['currency'] = ( isset($storeInfo['stores_info'][0]['currency']) ) ? $storeInfo['stores_info'][0]['currency']['iso3'] : '';
                    $res['selected_item'] = $item;

//                    Log::debug( print_r($res,1) );

                    if ( $res['type'] === 'configurable' ){
                        $pv = collect( $this->api2cart->getProductVariants( $pid[0], $pid[1] )['children'] );
                        foreach ($pv as $_pvitem){
                            $fields = [];
                            // check variant price
                            if ( isset($diff['price']) && $diff['price'] != $_pvitem['default_price'] ){
                                $fields['default_price'] = $request->get('price');
                            }
                            if (count($fields)) $this->api2cart->updateProductVariant($pid[0], $pid[1], $_pvitem['id'], $fields );
                        }
                        // reload variant data
                        $pv = collect( $this->api2cart->getProductVariants( $pid[0], $pid[1] )['children'] );
                        $res['children'] = $pv;
                    }

                    $result[] = $res;
                }
            }

            return response()->json(['items' => $result, 'log' => $this->api2cart->getLog()]);

        } else {

            $result = $this->api2cart->updateProduct( $store_id, $product_id, $diff );
            $storeInfo = $this->api2cart->getCart( $store_id );
            $result['currency'] = ( isset($storeInfo['stores_info'][0]['currency']) ) ? $storeInfo['stores_info'][0]['currency']['iso3'] : '';

            if ( $result['type'] === 'configurable' ){

                $pv = collect( $this->api2cart->getProductVariants($store_id, $product['id'] )['children'] );
                foreach ($request->get('children')['id'] as $k=>$_pvid){
                    $fields = [];
                    $pitem = $pv->where('id', $_pvid )->first();
                    // check variant price
                    if ( $request->get('children')['default_price'][$k] != $pitem['default_price'] ){
                        $fields['default_price'] = $request->get('children')['default_price'][$k];
                    }
                    if (count($fields)) $this->api2cart->updateProductVariant($store_id, $product_id, $_pvid, $fields );
                }
                // reload variants data
                $pv = collect( $this->api2cart->getProductVariants($store_id, $product['id'] )['children'] );
                $result['children'] = $pv;
            }

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
