<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Client;
use App\Models\User;


class Api2Cart
{

    private $host;
    private $apiKey;
    private $client;
    /**
     * Api2Cart constructor initiate with right API url
     */
    public function __construct()
    {
        $this->host = (env('API2CART_URL', "https://api.api2cart.com"));
        $this->client = new Client();
    }

    /**
     * Set User's API Key
     */
    private function setApiKey()
    {
        $this->apiKey = Auth::user()->api2cart_key;
    }


    public function checkConnection($apiKey)
    {



        try{

            $response = $this->client->request('GET', url($this->host) . "/v1.1/account.cart.list.json" , [
                'query' => ['api_key' => $apiKey]
            ]);

            $result = json_decode( $response->getBody()->getContents() ,true, 512, JSON_OBJECT_AS_ARRAY);

            if ( $result['return_code'] == 0 ) return true;
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

            $response = $this->client->request('GET', url($this->host) . "/v1.1/account.cart.list.json" , [
                'query' => [
                    'api_key'   => $this->apiKey,
                    'params'    => 'force_all'
                ]
            ]);

            $body = $response->getBody();

            return json_decode( $body->getContents() , true, 512, JSON_OBJECT_AS_ARRAY);


        } catch (\Exception $e){

            Log::debug( $e->getMessage() );

            return false;
        }
    }

    public function getCart($store_id)
    {
        $this->setApiKey();

        try{

            $response = $this->client->request('GET', url($this->host) . "/v1.1/cart.info.json" , [
                'query' => [
                    'api_key'   => $this->apiKey,
                    'store_key' => $store_id,
                    'params'    => 'store_name,store_url,stores_info'
                ]
            ]);

            $body = $response->getBody();

            return json_decode( $body->getContents() , true, 512, JSON_OBJECT_AS_ARRAY);


        } catch (\Exception $e){

            Log::debug( $e->getMessage() );

            return false;
        }
    }

    public function getCartsList()
    {
        $this->setApiKey();

        try{

            $response = $this->client->request('GET', url($this->host) . "/v1.1/cart.list.json" , [
                'query' => [
                    'api_key'   => $this->apiKey,
                ]
            ]);

            $body = $response->getBody();

            return json_decode( $body->getContents() , true, 512, JSON_OBJECT_AS_ARRAY);


        } catch (\Exception $e){

            Log::debug( $e->getMessage() );

            return false;
        }
    }


    public function getOrderCount( $store_id=null )
    {
        $this->setApiKey();

        try{

            $response = $this->client->request('GET', url($this->host) . "/v1.1/order.count.json" , [
                'query' => [ 'api_key' => $this->apiKey, 'store_key' => $store_id ]
            ]);

            $body = $response->getBody();

            return json_decode( $body->getContents() , true, 512, JSON_OBJECT_AS_ARRAY);


        } catch (\Exception $e){

            Log::debug( $e->getMessage() );

            return false;
        }

    }


    public function getOrderList( $store_id=null, $from=0, $numOrders=10 )
    {
        $this->setApiKey();

        try{

            $response = $this->client->request('GET', url($this->host) . "/v1.1/order.list.json" , [
                'query' => [
                    'api_key'   => $this->apiKey,
                    'store_key' => $store_id,
                    'start'     => $from,
                    'count'     => $numOrders,
                    'params'    => 'order_id,customer,totals,address,status'
                ]
            ]);

            $body = $response->getBody();

            return json_decode( $body->getContents() , true, 512, JSON_OBJECT_AS_ARRAY);


        } catch (\Exception $e){

            Log::debug( $e->getMessage() );

            return false;
        }

    }

    public function getOrderListPage( $store_id=null, $page_cursor=null )
    {
        $this->setApiKey();

        try{

            $response = $this->client->request('GET', url($this->host) . "/v1.1/order.list.json" , [
                'query' => [
                    'api_key'       => $this->apiKey,
                    'store_key'     => $store_id,
                    'page_cursor'   => $page_cursor,
                    'params'    => 'order_id,customer,totals,address,status'
                ]
            ]);

            $body = $response->getBody();

            return json_decode( $body->getContents() , true, 512, JSON_OBJECT_AS_ARRAY);


        } catch (\Exception $e){

            Log::debug( $e->getMessage() );

            return false;
        }

    }


    public function getProductCount($store_id=null)
    {
        $this->setApiKey();

        try{

            $response = $this->client->request('GET', url($this->host) . "/v1.1/product.count.json" , [
                'query' => [ 'api_key' => $this->apiKey, 'store_key' => $store_id ]
            ]);

            $body = $response->getBody();

            return json_decode( $body->getContents() , true, 512, JSON_OBJECT_AS_ARRAY);


        } catch (\Exception $e){

            Log::debug( $e->getMessage() );

            return false;
        }

    }

    public function getProductList($store_id=null, $from=0, $numOrders=10 )
    {
        $this->setApiKey();

        try{

            $response = $this->client->request('GET', url($this->host) . "/v1.1/product.list.json" , [
                'query' => [
                    'api_key'   => $this->apiKey,
                    'store_key' => $store_id,
                    'start'     => $from,
                    'count'     => $numOrders,
                    'params'    => 'force_all'
                ]
            ]);

            $body = $response->getBody();

            return json_decode( $body->getContents() , true, 512, JSON_OBJECT_AS_ARRAY);


        } catch (\Exception $e){

            Log::debug( $e->getMessage() );

            return false;
        }
    }

    public function getProductListPage( $store_id=null, $page_cursor=null )
    {
        $this->setApiKey();

        try{

            $response = $this->client->request('GET', url($this->host) . "/v1.1/product.list.json" , [
                'query' => [
                    'api_key'       => $this->apiKey,
                    'store_key'     => $store_id,
                    'page_cursor'   => $page_cursor,
                    'params'        => 'force_all'
                ]
            ]);

            $body = $response->getBody();

            return json_decode( $body->getContents() , true, 512, JSON_OBJECT_AS_ARRAY);


        } catch (\Exception $e){

            Log::debug( $e->getMessage() );

            return false;
        }
    }


    public function getCustomerCount($store_id=null)
    {
        $this->setApiKey();

        try{

            $response = $this->client->request('GET', url($this->host) . "/v1.1/customer.count.json" , [
                'query' => [ 'api_key' => $this->apiKey, 'store_key' => $store_id ]
            ]);

            $body = $response->getBody();

            return json_decode( $body->getContents() , true, 512, JSON_OBJECT_AS_ARRAY);


        } catch (\Exception $e){

            Log::debug( $e->getMessage() );

            return false;
        }

    }

    public function getCustomerList($store_id=null, $from=0, $numOrders=10 )
    {
        $this->setApiKey();

        try{

            $response = $this->client->request('GET', url($this->host) . "/v1.1/customer.list.json" , [
                'query' => [
                    'api_key'   => $this->apiKey,
                    'store_key' => $store_id,
                    'start'     => $from,
                    'count'     => $numOrders
                ]
            ]);

            $body = $response->getBody();

            return json_decode( $body->getContents() , true, 512, JSON_OBJECT_AS_ARRAY);


        } catch (\Exception $e){

            Log::debug( $e->getMessage() );

            return false;
        }
    }

    public function getCustomerListPage( $store_id=null, $page_cursor=null )
    {
        $this->setApiKey();

        try{

            $response = $this->client->request('GET', url($this->host) . "/v1.1/customer.list.json" , [
                'query' => [
                    'api_key'       => $this->apiKey,
                    'store_key'     => $store_id,
                    'page_cursor'   => $page_cursor,
                ]
            ]);

            $body = $response->getBody();

            return json_decode( $body->getContents() , true, 512, JSON_OBJECT_AS_ARRAY);


        } catch (\Exception $e){

            Log::debug( $e->getMessage() );

            return false;
        }
    }

}
