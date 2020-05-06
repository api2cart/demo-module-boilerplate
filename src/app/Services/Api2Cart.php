<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

use GuzzleHttp\Client;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\CurlHandler;


use App\Models\User;
use App\Models\Log as Logger;

use Api2Cart\Client as ApiClient;
use Api2Cart\Client\Model\Cart;


class Api2Cart
{
    private $config;


    private $account;
    private $cart;
    private $category;
    private $customer;
    private $order;
    private $product;

    private $log;


    /**
     * Api2Cart constructor initiate with right API objects
     */
    public function __construct()
    {
        $this->config = new ApiClient\Configuration();

        $this->account  = new ApiClient\Api\AccountApi(null, $this->config);
        $this->cart     = new ApiClient\Api\CartApi(null, $this->config );
        $this->category = new ApiClient\Api\CategoryApi( null, $this->config );
        $this->customer = new ApiClient\Api\CustomerApi( null, $this->config );
        $this->order    = new ApiClient\Api\OrderApi( null, $this->config );
        $this->product  = new ApiClient\Api\ProductApi( null, $this->config );

        $this->log = collect();

    }

    /**
     * Set User's API Key
     */
    private function setApiKey()
    {
        $this->config->setApiKey( 'api_key', Auth::user()->api2cart_key );
//        $this->config->setApiKey( 'api_key', '948c024602b4912149c708fdcbbab5d8' );
    }


    /**
     * Check connection to API uses given API Key
     * @param null $apiKey
     * @return bool
     */
    public function checkConnection($apiKey=null)
    {

        try{


            $this->config->setApiKey( 'api_key', $apiKey);

            $result = $this->account->accountCartList();

            $this->logApiCall( 'account.cart.list.json', $result->getReturnCode(), $this->account->getConfig(), null, null, null, $result->getReturnMessage()  );

            if ( $result->getReturnCode() == 0 ) {
                return true;
            }
            else {
                return false;
            }


        } catch (\Exception $e){

//            Log::debug( $e->getMessage() );
            $this->logApiCall( 'account.cart.list.json', $e->getCode(), $this->account->getConfig(), null, null, null, $e->getMessage()  );
            return false;
        }


    }

    /**
     * Get account carts
     *
     * @return array|bool|mixed|null
     */
    public function getCartList()
    {
        $this->setApiKey();

        try{

            $result = $this->account->accountCartList();

            $this->logApiCall( 'account.cart.list.json', $result->getReturnCode(), $this->account->getConfig(), null, null, null, $result->getReturnMessage()  );

            if ( $result->getResult()->getCartsCount() ){
                return $this->mapToArray( $result->getResult()->getCarts() );
            } else {
                return null;
            }



        } catch (\Exception $e){

//            Log::debug( $e->getMessage() );
            $this->logApiCall( 'account.cart.list.json', $e->getCode(), $this->account->getConfig(), null, null, null, $e->getMessage()  );

            return null;
        }
    }

    /**
     * Get cart details
     *
     * @param $store_id
     * @return Cart|bool|null
     */
    public function getCart($store_id)
    {
        $this->setApiKey();

        try{

            $this->cart->getConfig()->setApiKey('store_key', $store_id);

            $result = $this->cart->cartInfo( 'force_all','additional_fields', $store_id);

            $this->logApiCall( 'cart.info.json', $result->getReturnCode(), $this->cart->getConfig(), null, null, null, $result->getReturnMessage() );

            if ( $result->getReturnCode() == 0 ){
                /**
                 * return object cause it cant be right maped to array...  swagger issue
                 */
                return $result->getResult();
//                return json_decode( $result->getResult()->__toString() , true, 512, JSON_OBJECT_AS_ARRAY) ;
            } else {
                return null;
            }



        } catch (\Exception $e){

//            Log::debug( $e->getMessage() );
            $this->logApiCall( 'cart.info.json', $e->getCode(), $this->account->getConfig(), null, null, null, $e->getMessage()  );

            return null;
        }
    }

    /**
     * Get list of supported carts
     *
     * @return array|bool|mixed|null
     */
    public function getCartsList()
    {
        $this->setApiKey();

        try{

//            $result = $this->cart->cartList();
            $result = $this->account->accountSupportedPlatforms();

            $this->logApiCall( 'cart.list.json', $result->getReturnCode(), $this->cart->getConfig(), null, null, null, $result->getReturnMessage()  );

            if ( $result->getReturnCode() == 0 ){
                return $this->mapToArray( $result->getResult()->getSupportedPlatforms() );
            } else {
                return null;
            }


        } catch (\Exception $e){

//            Log::debug( $e->getMessage() );
            $this->logApiCall( 'cart.list.json', $e->getCode(), $this->account->getConfig(), null, null, null, $e->getMessage()  );
            return false;
        }
    }

    public function deleteCart($store_id)
    {
        $this->setApiKey();

        try{

            $this->cart->getConfig()->setApiKey('store_key', $store_id);
            $result = $this->cart->cartDelete();

            $this->logApiCall( 'cart.delete.json', $result->getReturnCode(), $this->cart->getConfig(), null, null, null, $result->getReturnMessage()  );

            if ( $result->getReturnCode() == 0 ){
                return true;
            } else {
                return null;
            }



        } catch (\Exception $e){

//            Log::debug( $e->getMessage() );
            $this->logApiCall( 'cart.delete.json', $e->getCode(), $this->account->getConfig(), null, null, null, $e->getMessage()  );
            return false;
        }
    }

    public function getCategoryCount( $store_id=null )
    {
        $this->setApiKey();

        try{

            $this->order->getConfig()->setApiKey('store_key', $store_id);

            $result = $this->category->categoryCount();

            $this->logApiCall( 'category.count.json', $result->getReturnCode(), $this->order->getConfig(), null, null, null, $result->getReturnMessage()  );

            if ( $result->getReturnCode() == 0 ){
                return $result->getResult()->getCategoriesCount();
            } else {
                return false;
            }


        } catch (\Exception $e){

//            Log::debug( $e->getMessage() );
            $this->logApiCall( 'category.count.json', $e->getCode(), $this->account->getConfig(), null, null, null, $e->getMessage()  );
            return false;
        }
    }

    public function getCategoryList( $store_id=null  )
    {
        $this->setApiKey();

        try{

            $this->order->getConfig()->setApiKey('store_key', $store_id);

            $result = $this->category->categoryList( null, null, null);

            $this->logApiCall( 'category.list.json', $result->getReturnCode(), $this->order->getConfig(), null, null, null, $result->getReturnMessage()  );

            if ( $result->getReturnCode() == 0 ){
                return $this->mapToArray( $result );
            } else {
                return false;
            }



        } catch (\Exception $e){

//            Log::debug( $e->getMessage() );
            $this->logApiCall( 'category.list.json', $e->getCode(), $this->account->getConfig(), null, null, null, $e->getMessage()  );
            return false;
        }

    }

    public function getCategoryListPage( $store_id=null, $page_cursor=null  )
    {
        $this->setApiKey();

        try{

            $this->order->getConfig()->setApiKey('store_key', $store_id);

            $result = $this->category->categoryList( null, null, $page_cursor);

            $this->logApiCall( 'category.list.json', $result->getReturnCode(), $this->order->getConfig(), null, null, null, $result->getReturnMessage()  );

            if ( $result->getReturnCode() == 0 ){
                return $this->mapToArray( $result );
            } else {
                return false;
            }



        } catch (\Exception $e){

//            Log::debug( $e->getMessage() );
            $this->logApiCall( 'category.list.json', $e->getCode(), $this->account->getConfig(), null, null, null, $e->getMessage()  );
            return false;
        }

    }

    public function getCategoryInfo( $store_id=null, $category_id=null )
    {
        $this->setApiKey();

        try{

            $this->order->getConfig()->setApiKey('store_key', $store_id);

            $result = $this->category->categoryInfo($category_id);

            $this->logApiCall( 'category.info.json', $result->getReturnCode(), $this->order->getConfig(), null, null, null, $result->getReturnMessage()  );

            if ( $result->getReturnCode() == 0 ){
                return $this->mapToArray( $result->getResult() );
            } else {
                return false;
            }


        } catch (\Exception $e){

//            Log::debug( $e->getMessage() );
            $this->logApiCall( 'category.info.json', $e->getCode(), $this->account->getConfig(), null, null, null, $e->getMessage()  );
            return false;
        }

    }

    public function updateCategory($store_id=null, $category_id=null, $fields=[])
    {
        $this->setApiKey();

        try{

            $this->order->getConfig()->setApiKey('store_key', $store_id);

            if ( $fields ){

                $result = $this->category->categoryUpdate(
                    $category_id,
                    (isset($fields['name'])) ? $fields['name'] : null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    (isset($fields['description'])) ? $fields['description'] : null,
                    null,
                    null,
                    null,
                    null
                );

                $this->logApiCall( 'category.update.json', $result->getReturnCode(), $this->product->getConfig(), null, null, null, $result->getReturnMessage()  );

                if ( $result->getReturnCode() == 0 ){

                    return $this->getCategoryInfo($store_id,$category_id);
                } else {
                    return null;
                }


            } else {
                return null;
            }


        } catch (\Exception $e){

//            Log::debug( $e->getMessage() );
            $this->logApiCall( 'category.update.json', $e->getCode(), $this->account->getConfig(), null, null, null, $e->getMessage()  );
            return false;
        }

    }

    public function deleteCategory($store_id=null, $category_id=null)
    {
        $this->setApiKey();

        if ( !$store_id || !$category_id ) return false;

        try{

            $this->order->getConfig()->setApiKey('store_key', $store_id);

            $result = $this->category->categoryDelete( $category_id );

            $this->logApiCall( 'category.delete.json', $result->getReturnCode(), $this->product->getConfig(), null, null, null, $result->getReturnMessage()  );


            if ( $result->getReturnCode() == 0 ){
                return true;
            } else {
                return false;
            }




        } catch (\Exception $e){

//            Log::debug( $e->getMessage() );
            $this->logApiCall( 'category.delete.json', $e->getCode(), $this->account->getConfig(), null, null, null, $e->getMessage()  );
            return false;
        }

    }

    public function getOrderCount( $store_id=null )
    {
        $this->setApiKey();

        try{

            $this->order->getConfig()->setApiKey('store_key', $store_id);

            $result = $this->order->orderCount();

            $this->logApiCall( 'order.count.json', $result->getReturnCode(), $this->order->getConfig(), null, null, null, $result->getReturnMessage()  );

            if ( $result->getReturnCode() == 0 ){
                return $result->getResult()->getOrdersCount();
            } else {
                return false;
            }


        } catch (\Exception $e){

//            Log::debug( $e->getMessage() );
            $this->logApiCall( 'order.count.json', $e->getCode(), $this->account->getConfig(), null, null, null, $e->getMessage()  );
            return false;
        }

    }

    public function getOrderList( $store_id=null  )
    {
        $this->setApiKey();

        try{

            $this->order->getConfig()->setApiKey('store_key', $store_id);

            $result = $this->order->orderList( null, null, null, null,null,null,null,null,'order_id,customer,totals,address,items,bundles,status,currency');

            $this->logApiCall( 'order.list.json', $result->getReturnCode(), $this->order->getConfig(), null, null, null, $result->getReturnMessage()  );

            if ( $result->getReturnCode() == 0 ){
                return $this->mapToArray( $result );
            } else {
                return false;
            }



        } catch (\Exception $e){

//            Log::debug( $e->getMessage() );
            $this->logApiCall( 'order.list.json', $e->getCode(), $this->account->getConfig(), null, null, null, $e->getMessage()  );
            return false;
        }

    }

    public function getOrderListPage( $store_id=null, $page_cursor=null )
    {
        $this->setApiKey();

        try{


            $this->order->getConfig()->setApiKey('store_key', $store_id);

            $result = $this->order->orderList( null, null, null, null, null, $page_cursor, null, null, 'order_id,customer,totals,address,items,bundles,status,currency' );

            $this->logApiCall( 'order.list.json', $result->getReturnCode(), $this->order->getConfig(), null, null, null, $result->getReturnMessage()  );

            if ( $result->getReturnCode() == 0 ){
                return $this->mapToArray( $result );
            } else {
                return false;
            }



        } catch (\Exception $e){

//            Log::debug( $e->getMessage() );
            $this->logApiCall( 'order.list.json', $e->getCode(), $this->account->getConfig(), null, null, null, $e->getMessage()  );
            return false;
        }

    }

    public function getOrderInfo( $store_id=null, $order_id=null )
    {
        $this->setApiKey();

        try{

            $this->order->getConfig()->setApiKey('store_key', $store_id);

            $result = $this->order->orderInfo( $order_id ,'force_all');

            $this->logApiCall( 'order.info.json', $result->getReturnCode(), $this->order->getConfig(), null, null, null, $result->getReturnMessage()  );

            if ( $result->getReturnCode() == 0 ){
                return $this->mapToArray( $result->getResult() );
            } else {
                return false;
            }


        } catch (\Exception $e){

//            Log::debug( $e->getMessage() );
            $this->logApiCall( 'order.info.json', $e->getCode(), $this->account->getConfig(), null, null, null, $e->getMessage()  );
            return false;
        }

    }

    public function getProductCount($store_id=null)
    {
        $this->setApiKey();

        try{

            $this->order->getConfig()->setApiKey('store_key', $store_id);

            $result = $this->product->productCount();

            $this->logApiCall( 'product.count.json', $result->getReturnCode(), $this->product->getConfig(), null, null, null, $result->getReturnMessage()  );

            if ( $result->getReturnCode() == 0 ){
                return $result->getResult()->getProductsCount();
            } else {
                return false;
            }




        } catch (\Exception $e){

//            Log::debug( $e->getMessage() );
            $this->logApiCall( 'product.count.json', $e->getCode(), $this->account->getConfig(), null, null, null, $e->getMessage()  );
            return false;
        }

    }

    public function getProductList($store_id=null, $ids=null )
    {
        $this->setApiKey();

        try{

            if ($ids) {
                // convert to string if array given
                if ( is_array($ids) ){
                    $ids = implode( ",", $ids);
                }
            }

            $this->order->getConfig()->setApiKey('store_key', $store_id);

            $result = $this->product->productList( null, null, null, 'force_all',null, null, null, null, null, null, null, null, null, null, null, $ids, null, null, null, null, null, null  );

            $this->logApiCall( 'product.list.json', $result->getReturnCode(), $this->product->getConfig(), null, null, null, $result->getReturnMessage()  );

            if ( $result->getReturnCode() == 0 ){
                return $this->mapToArray( $result );
            } else {

                Log::debug( print_r( $this->product->getConfig()->getDebug(),1 ) );

                return false;
            }




        } catch (\Exception $e){

//            Log::debug( $e->getMessage() );
            $this->logApiCall( 'product.list.json', $e->getCode(), $this->account->getConfig(), null, null, null, $e->getMessage()  );
            return false;
        }
    }

    public function getProductListPage( $store_id=null, $page_cursor=null )
    {
        $this->setApiKey();

        try{

            $this->order->getConfig()->setApiKey('store_key', $store_id);

            $result = $this->product->productList( $page_cursor, null, null, 'force_all',null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null  );

            $this->logApiCall( 'product.list.json', $result->getReturnCode(), $this->product->getConfig(), null, null, null, $result->getReturnMessage()  );

            if ( $result->getReturnCode() == 0 ){
                return $this->mapToArray( $result );
            } else {

                Log::debug( print_r( $this->product->getConfig()->getDebug(),1 ) );

                return false;
            }



        } catch (\Exception $e){

//            Log::debug( $e->getMessage() );
            $this->logApiCall( 'product.list.json', $e->getCode(), $this->account->getConfig(), null, null, null, $e->getMessage()  );
            return false;
        }
    }

    public function getProductInfo($store_id=null, $product_id=null)
    {
        $this->setApiKey();

        try{

            $this->order->getConfig()->setApiKey('store_key', $store_id);
            $result = $this->product->productInfo($product_id,'force_all');

            $this->logApiCall( 'product.info.json', $result->getReturnCode(), $this->product->getConfig(), null, null, null, $result->getReturnMessage()  );

            if ( $result->getReturnCode() == 0 ){
                return $this->mapToArray( $result->getResult() );
            } else {
                return null;
            }



        } catch (\Exception $e){

//            Log::debug( $e->getMessage() );
            $this->logApiCall( 'product.info.json', $e->getCode(), $this->account->getConfig(), null, null, null, $e->getMessage()  );
            return false;
        }

    }

    public function updateProduct($store_id=null, $product_id=null, $fields=[])
    {
        $this->setApiKey();

        try{

            $this->order->getConfig()->setApiKey('store_key', $store_id);

            if ( $fields ){

                $result = $this->product->productUpdate(
                    $product_id,
                    null,
                    (isset($fields['price'])) ? $fields['price'] : null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    (isset($fields['name'])) ? $fields['name'] : null,
                    null,
                    null,
                    null,
                    null,
                    (isset($fields['description'])) ? $fields['description'] : null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null
                );

//                Log::debug( print_r($result,1));

                $this->logApiCall( 'product.update.json', $result->getReturnCode(), $this->product->getConfig(), null, null, null, $result->getReturnMessage()  );

                if ( $result->getReturnCode() == 0 ){

                    return $this->getProductInfo($store_id,$product_id);
                } else {
                    return null;
                }


            } else {
                return null;
            }


        } catch (\Exception $e){

//            Log::debug( $e->getMessage() );
            $this->logApiCall( 'product.update.json', $e->getCode(), $this->account->getConfig(), null, null, null, $e->getMessage()  );
            return false;
        }

    }

    public function deleteProduct($store_id=null, $product_id=null)
    {
        $this->setApiKey();

        if ( !$store_id || !$product_id ) return false;

        try{

            $this->order->getConfig()->setApiKey('store_key', $store_id);

            $result = $this->product->productDelete( $product_id );

            $this->logApiCall( 'product.delete.json', $result->getReturnCode(), $this->product->getConfig(), null, null, null, $result->getReturnMessage()  );


            if ( $result->getReturnCode() == 0 ){
                return true;
            } else {
                return false;
            }




        } catch (\Exception $e){

//            Log::debug( $e->getMessage() );
            $this->logApiCall( 'product.delete.json', $e->getCode(), $this->account->getConfig(), null, null, null, $e->getMessage()  );
            return false;
        }

    }

    public function deleteProductImage($store_id=null, $product_id=null, $image_id=null)
    {
        $this->setApiKey();

        if ( !$store_id || !$product_id || !$image_id) return false;

        try{

            $this->order->getConfig()->setApiKey('store_key', $store_id);

            $product    = $this->product->productInfo($product_id,'force_all')->getResult();
            $images     = $product->getImages();

//            Log::debug( print_r($images ,1) );

            foreach ($images as $k=>$item){
                if ( $item->getId() === $image_id ){
                    $result = $this->product->productImageDelete( $product_id, $k, $store_id);
                    //TODO: fix right delete images after API be changed
//                    Log::debug( print_r($result,1) );
                }
            }

//            $result = $this->product->productDelete( $product_id );
//            $this->product->productImageDelete();

//            $this->logApiCall( 'product.delete.json', $result->getReturnCode(), $this->product->getConfig(), null, null, null, $result->getReturnMessage()  );
//
//
//            if ( $result->getReturnCode() == 0 ){
//                return true;
//            } else {
//                return false;
//            }




        } catch (\Exception $e){

            Log::debug( $e->getMessage() );

            return false;
        }

    }

    public function getCustomerCount($store_id=null)
    {
        $this->setApiKey();

        try{

            $this->order->getConfig()->setApiKey('store_key', $store_id);

            $result = $this->customer->customerCount();

            $this->logApiCall( 'customer.count.json', $result->getReturnCode(), $this->customer->getConfig(), null, null, null, $result->getReturnMessage()  );

            if ( $result->getReturnCode() == 0 ){
                return $result->getResult()->getCustomersCount();
            } else {
                return false;
            }

        } catch (\Exception $e){

//            Log::debug( $e->getMessage() );
            $this->logApiCall( 'customer.count.json', $e->getCode(), $this->account->getConfig(), null, null, null, $e->getMessage()  );
            return false;
        }

    }

    public function getCustomerList($store_id=null)
    {
        $this->setApiKey();

        try{

            $this->order->getConfig()->setApiKey('store_key', $store_id);

            $result = $this->customer->customerList( null, null, null, null,null,null,null,'force_all', null,  null, null, null);

            $this->logApiCall( 'customer.list.json', $result->getReturnCode(), $this->customer->getConfig(), null, null, null, $result->getReturnMessage()  );

            if ( $result->getReturnCode() == 0 ){
                return $this->mapToArray( $result );
            } else {
                return false;
            }




        } catch (\Exception $e){

//            Log::debug( $e->getMessage() );
            $this->logApiCall( 'customer.list.json', $e->getCode(), $this->account->getConfig(), null, null, null, $e->getMessage()  );
            return false;
        }
    }

    public function getCustomerListPage( $store_id=null, $page_cursor=null )
    {
        $this->setApiKey();

        try{

            $this->order->getConfig()->setApiKey('store_key', $store_id);

            $result = $this->customer->customerList( $page_cursor, null, null, null, null, null, null, 'force_all', null, null, null, null );

            $this->logApiCall( 'customer.list.json', $result->getReturnCode(), $this->customer->getConfig(), null, null, null, $result->getReturnMessage()  );

            if ( $result->getReturnCode() == 0 ){
                return $this->mapToArray( $result );
            } else {
                return false;
            }


        } catch (\Exception $e){

//            Log::debug( $e->getMessage() );
            $this->logApiCall( 'customer.list.json', $e->getCode(), $this->account->getConfig(), null, null, null, $e->getMessage()  );
            return false;
        }
    }


    private function mapToArray($data=null)
    {
        if ($data == null) return null;

        if ( is_array($data) ){
            return array_map(function($item){

                try {

                    return json_decode( $item->__toString() , true, 512, JSON_OBJECT_AS_ARRAY);

                } catch (\Exception $e){
                    return $item;
                }



            }, $data);

        }

        if ( is_object($data) ){

            try{
                return json_decode( $data->__toString() , true, 512, JSON_OBJECT_AS_ARRAY);
            } catch (\Exception $e){
                return $data;
            }


        }




    }


    private function logApiCall( $action=null, $code=null, $config = null, $store_id=null, $store_ur=null, $user_id=null, $msg=null)
    {
        $log = Logger::create([
            'action'    => $action,
            'code'      => $code,
            'params'    => (is_object($config)) ? ['api_key' => $config->getApiKey('api_key'), 'store_key' => $config->getApiKey('store_key'), 'msg' => $msg ] : [],
            'store_id'  => ($store_id) ? $store_id : $config->getApiKey('store_key'),
            'store_url' => $store_ur,
            'user_id'   => $user_id
        ]);

        $this->log->push( $log );

    }

    public function getLog()
    {
        return $this->log;
    }


    public function test()
    {
        $this->setApiKey();

        try{

            $this->order->getConfig()->setApiKey('store_key', 'e20f7debea000c989e4583025c996309');

            dd( $this->getCartsList() );

//            dd( $this->getCategoryCount('e20f7debea000c989e4583025c996309') );

//            $result = $this->category->categoryList();
//            dd($result);

//            $result = $this->product->productFields();

//            return $this->mapToArray( $result->getResult() );

//            if ( $result->getResult() ){
//                return $this->mapToArray( $result->getResult()->getCarts() );
//            } else {
//                return null;
//            }



        } catch (\Exception $e){

            Log::debug( $e->getMessage() );

            return false;
        }
    }
}
