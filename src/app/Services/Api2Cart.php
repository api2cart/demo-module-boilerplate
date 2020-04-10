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

            if ( $this->account->accountCartList()->getReturnCode() == 0 ) return true;
            else return false;


        } catch (\Exception $e){

            Log::debug( $e->getMessage() );

            return false;
        }


    }

    public function getCartList()
    {
        $this->setApiKey();

        try{

            $result = $this->account->accountCartList()->getResult();

            if ( $result->getCartsCount() ){
                return $this->mapToArray( $result->getCarts() );
            } else {
                return null;
            }



        } catch (\Exception $e){

            Log::debug( $e->getMessage() );

            return false;
        }
    }

    public function getCart($store_id)
    {
        $this->setApiKey();

        try{

            $this->cart->getConfig()->setApiKey('store_key', $store_id);

            $result = $this->cart->cartInfo( 'force_all','additional_fields', $store_id);

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

            Log::debug( $e->getMessage() );

            return false;
        }
    }

    public function getCartsList()
    {
        $this->setApiKey();

        try{

            $result = $this->cart->cartList();

            if ( $result->getReturnCode() == 0 ){
                return $this->mapToArray( $result->getResult()->getSupportedCarts() );
            } else {
                return null;
            }


        } catch (\Exception $e){

            Log::debug( $e->getMessage() );

            return false;
        }
    }

    public function getOrderCount( $store_id=null )
    {
        $this->setApiKey();

        try{

            $this->order->getConfig()->setApiKey('store_key', $store_id);

            $result = $this->order->orderCount();

            if ( $result->getReturnCode() == 0 ){
                return $result->getResult()->getOrdersCount();
            } else {
                return false;
            }


        } catch (\Exception $e){

            Log::debug( $e->getMessage() );

            return false;
        }

    }

    public function getOrderList( $store_id=null  )
    {
        $this->setApiKey();

        try{

            $this->order->getConfig()->setApiKey('store_key', $store_id);

            $result = $this->order->orderList( null, null, null, null,null,null,null,null,'order_id,customer,totals,address,items,bundles,status,currency');


            if ( $result->getReturnCode() == 0 ){
                return $this->mapToArray( $result );
            } else {
                return false;
            }



        } catch (\Exception $e){

            Log::debug( $e->getMessage() );

            return false;
        }

    }

    public function getOrderListPage( $store_id=null, $page_cursor=null )
    {
        $this->setApiKey();

        try{


            $this->order->getConfig()->setApiKey('store_key', $store_id);

            $result = $this->order->orderList( null, null, null, null, null, $page_cursor, null, null, 'order_id,customer,totals,address,items,bundles,status,currency' );

            if ( $result->getReturnCode() == 0 ){
                return $this->mapToArray( $result );
            } else {
                return false;
            }



        } catch (\Exception $e){

            Log::debug( $e->getMessage() );

            return false;
        }

    }

    public function getProductCount($store_id=null)
    {
        $this->setApiKey();

        try{

            $this->order->getConfig()->setApiKey('store_key', $store_id);

            $result = $this->product->productCount();

            if ( $result->getReturnCode() == 0 ){
                return $result->getResult()->getProductsCount();
            } else {
                return false;
            }




        } catch (\Exception $e){

            Log::debug( $e->getMessage() );

            return false;
        }

    }

    public function getProductList($store_id=null )
    {
        $this->setApiKey();

        try{

            $this->order->getConfig()->setApiKey('store_key', $store_id);

            $result = $this->product->productList( null, null, null, 'force_all',null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null  );


            if ( $result->getReturnCode() == 0 ){
                return $this->mapToArray( $result );
            } else {
                return false;
            }




        } catch (\Exception $e){

            Log::debug( $e->getMessage() );

            return false;
        }
    }

    public function getProductListPage( $store_id=null, $page_cursor=null )
    {
        $this->setApiKey();

        try{

            $this->order->getConfig()->setApiKey('store_key', $store_id);

            $result = $this->product->productList( $page_cursor, null, null, 'force_all',null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null  );


            if ( $result->getReturnCode() == 0 ){
                return $this->mapToArray( $result );
            } else {
                return false;
            }



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

            if ( $result->getReturnCode() == 0 ){
                return $result->getResult()->getCustomersCount();
            } else {
                return false;
            }

        } catch (\Exception $e){

            Log::debug( $e->getMessage() );

            return false;
        }

    }

    public function getCustomerList($store_id=null)
    {
        $this->setApiKey();

        try{

            $this->order->getConfig()->setApiKey('store_key', $store_id);

            $result = $this->customer->customerList( null, null, null, null,null,null,null,'force_all', null);


            if ( $result->getReturnCode() == 0 ){
                return $this->mapToArray( $result );
            } else {
                return false;
            }




        } catch (\Exception $e){

            Log::debug( $e->getMessage() );

            return false;
        }
    }

    public function getCustomerListPage( $store_id=null, $page_cursor=null )
    {
        $this->setApiKey();

        try{

            $this->order->getConfig()->setApiKey('store_key', $store_id);

            $result = $this->customer->customerList( null, null, null, null, null, $page_cursor, null, 'force_all', null );

            if ( $result->getReturnCode() == 0 ){
                return $this->mapToArray( $result );
            } else {
                return false;
            }


        } catch (\Exception $e){

            Log::debug( $e->getMessage() );

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


}
