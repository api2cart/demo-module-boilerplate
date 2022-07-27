<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Services\Api2Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductsController extends Controller
{
    private $api2cart;

    /**
     * ProductsController constructor.
     * @param Api2Cart $api2Cart
     */
    public function __construct(Api2Cart $api2Cart)
    {
        $this->api2cart = $api2Cart;
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('products.index');
    }

    /**
     * @param string|null $store_id Store ID
     * @param Request     $request  Request
     * @return \Illuminate\Http\JsonResponse
     */
    public function productList($store_id=null,Request $request)
    {
        \Debugbar::disable();

        $storeInfo = $this->api2cart->getCart( $store_id );

        $sort_by      = ($request->get('sort_by')) ? 'create_at' : null;
        $sort_direct  = ($request->get('sort_direct')) ? true : false;
        $created_from = ($request->get('created_from')) ? $request->get('created_from') : null;
        $limit        = ($request->get('limit')) ? $request->get('limit') : null;

        $totalProducts = $this->api2cart->getProductCount( $store_id );

        $products = collect([]);

        if ( $totalProducts ){

            $result = $this->api2cart->getProductList( $store_id, null, null, null, null , $created_from );

            $newRes= (isset($result['result']['products_count'])) ? collect( $result['result']['product'] ) : collect([]);
            // put additional information
            if ( $newRes->count() ){
                foreach ($newRes as $item){
                    $newItem = $item;
                    $newItem['currency'] = ( isset($storeInfo['stores_info'][0]['currency']) ) ? $storeInfo['stores_info'][0]['currency']['iso3'] : '';

                    // collect product variants
                    if ( isset($item['type']) && $item['type'] === 'configurable' ){
                        $pv = $this->api2cart->getProductVariants($store_id, $item['id'] );
                        $newItem['children'] = [];

                        if (!empty($pv['children'])) {
                            $childrens = collect($pv['children']);
                            $minPrice = $childrens->where('default_price', $childrens->min('default_price'))->first();

                            if ($minPrice) {
                                $newItem['children']['min'] = $minPrice;
                            }

                            $maxPrice = $childrens->where('default_price', $childrens->max('default_price'))->first();

                            if ($maxPrice && isset($newItem['children']['min']) && $newItem['children']['min']['default_price'] < $maxPrice['default_price']) {
                                $newItem['children']['max'] = $maxPrice;
                            }
                        }

                        $newItem['children'] = array_values($newItem['children']);
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
                            if (isset($item['type']) && $item['type'] === 'configurable') {
                                $pv = $this->api2cart->getProductVariants($store_id, $item['id'] );
                                $newItem['children'] = [];

                                if (!empty($pv['children'])) {
                                    $childrens = collect($pv['children']);
                                    $minPrice = $childrens->where('default_price', $childrens->min('default_price'))->first();

                                    if ($minPrice) {
                                        $newItem['children']['min'] = $minPrice;
                                    }

                                    $maxPrice = $childrens->where('default_price', $childrens->max('default_price'))->first();

                                    if ($maxPrice && isset($newItem['children']['min']) && $newItem['children']['min']['default_price'] < $maxPrice['default_price']) {
                                        $newItem['children']['max'] = $maxPrice;
                                    }
                                }

                                $newItem['children'] = array_values($newItem['children']);
                            }

                            $products->push( $newItem );
                        }
                    }
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
            $sorted = $products->sortBy($sort_by, null, $sort_direct );
        } else {
            $sorted = $products->sortBy('create_at.value', null, $sort_direct );
        }

        $data = [
            "recordsTotal"      => $totalProducts,
            "recordsFiltered"   => $totalProducts,
            "start"             => 0,
            "length"            => 10,
            "data"              => ($limit) ? $sorted->forPage(0, $limit)->toArray() : $products->toArray(),

            'log'               => $this->api2cart->getLog(),
        ];

        return response()->json($data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param string|null    $store_id   Store ID
     * @param string|null    $product_id Product ID
     * @param ProductRequest $request    Request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Throwable
     */
    public function edit($store_id=null, $product_id=null, Request $request)
    {
        \Debugbar::disable();

        $product = $this->api2cart->getProductInfo($store_id, $product_id);

        if ( $product['type'] === 'configurable' ){
            $pv = $this->api2cart->getProductVariants($store_id, $product['id'] );
            $product['children'] = $pv['children'];
        }

        if ( $request->ajax() ){
            return response()->json(['data' => view('products.form',compact('product','store_id', 'product_id'))->render(), 'item' => $product,'log' => $this->api2cart->getLog() ]);
        }

        return redirect(route('products.index'));
    }

    /**
     * @param string|null    $store_id   Store ID
     * @param string|null    $product_id Product ID
     * @param ProductRequest $request    Request
     *
     * @return \Illuminate\Http\JsonResponse
     */
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
     * @param string|null $store_id   Store ID
     * @param string|null $product_id Product ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($store_id=null, $product_id=null)
    {
        if ( $this->api2cart->deleteProduct($store_id , $product_id ) ){
            return response()->json([ 'log' => $this->api2cart->getLog() ]);
        } else {
            return response()->json([ 'log' => $this->api2cart->getLog() ], 404);
        }
    }

}
