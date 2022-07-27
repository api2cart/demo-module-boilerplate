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
use Api2Cart\Client\Model\AccountCartAdd;


class Api2Cart
{
    private $config;

    public $debug = true;

    private $account;
    private $cart;
    private $category;
    private $customer;
    private $order;
    private $product;
    private $subscriber;

    private $log;
    private $isTest;

    /**
     * Api2Cart constructor initiate with right API objects
     */
    public function __construct($isTest=false)
    {
        $this->isTest = $isTest;

        $this->config = new ApiClient\Configuration();
        $this->config->setHost(env('API2CART_URL', 'https://api.api2cart.com/v1.1'));

        $this->account  = new ApiClient\Api\AccountApi(null, $this->config);
        $this->cart     = new ApiClient\Api\CartApi(null, $this->config );
        $this->category = new ApiClient\Api\CategoryApi( null, $this->config );
        $this->customer = new ApiClient\Api\CustomerApi( null, $this->config );
        $this->order    = new ApiClient\Api\OrderApi( null, $this->config );
        $this->product  = new ApiClient\Api\ProductApi( null, $this->config );
        $this->subscriber = new ApiClient\Api\SubscriberApi(null, $this->config );

        $this->log = collect();

    }

    /**
     * Set User's API Key
     */
    public function setApiKey($api_key=null)
    {
        $api_key = ($api_key) ? $api_key : Auth::user()->api2cart_key;
        $this->config->setApiKey( 'api_key', $api_key );
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
            $this->logApiCall( 'account.cart.list.json', $result->getResult(), $result->getReturnCode(), $this->account->getConfig(), null, null, null, $result->getReturnMessage()  );

            if ($result->getReturnCode() == 0) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e){
            $this->logApiCall( 'account.cart.list.json', [], $e->getCode(), $this->account->getConfig(), null, null, null, $e->getMessage()  );
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

            $this->logApiCall( 'account.cart.list.json', $result->getResult(), $result->getReturnCode(), $this->account->getConfig(), null, null, null, $result->getReturnMessage()  );

            if ($result->getResult()->getCartsCount()) {
                return $this->mapToArray($result->getResult()->getCarts());
            } else {
                if ($this->debug) {
                    Log::debug(print_r($result, 1));
                }

                return null;
            }

        } catch (\Exception $e){
//            Log::debug( $e->getMessage() );
            $this->logApiCall( 'account.cart.list.json', [], $e->getCode(), $this->account->getConfig(), null, null, null, $e->getMessage()  );

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
            $result = $this->cart->cartInfo( 'force_all','additional_fields');

            $this->logApiCall( 'cart.info.json', $result->getResult(), $result->getReturnCode(), $this->cart->getConfig(), null, null, null, $result->getReturnMessage() );

            if ($result->getReturnCode() == 0) {
                /**
                 * return object cause it cant be right maped to array...  swagger issue
                 */
                return $this->mapToArray($result->getResult());
//                return json_decode( $result->getResult()->__toString() , true, 512, JSON_OBJECT_AS_ARRAY) ;
            } else {
                if ($this->debug) {
                    Log::debug(print_r($result, 1));
                }

                return null;
            }
        } catch (\Exception $e){
//            Log::debug( $e->getMessage() );
            $this->logApiCall( 'cart.info.json', [], $e->getCode(), $this->account->getConfig(), null, null, null, $e->getMessage()  );

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
            $this->logApiCall( 'account.supported_platforms.json', $result->getResult(), $result->getReturnCode(), $this->cart->getConfig(), null, null, null, $result->getReturnMessage()  );

            if ($result->getReturnCode() == 0) {
                return $this->mapToArray($result->getResult()->getSupportedPlatforms());
            } else {
                if ($this->debug) {
                    Log::debug(print_r($result, 1));
                }

                return null;
            }
        } catch (\Exception $e){
//            Log::debug( $e->getMessage() );
            $this->logApiCall( 'account.supported_platforms.json', [], $e->getCode(), $this->account->getConfig(), null, null, null, $e->getMessage()  );
            return false;
        }
    }

    /**
     * @param $store_id
     * @return bool|null
     */
    public function deleteCart($store_id)
    {
        $this->setApiKey();

        try{
            $this->cart->getConfig()->setApiKey('store_key', $store_id);
            $result = $this->cart->cartDelete();
            $this->logApiCall( 'cart.delete.json', $result->getResult(), $result->getReturnCode(), $this->cart->getConfig(), null, null, null, $result->getReturnMessage()  );

            if ($result->getReturnCode() == 0) {
                return true;
            } else {
                if ($this->debug) {
                    Log::debug(print_r($result, 1));
                }

                return null;
            }
        } catch (\Exception $e){
//            Log::debug( $e->getMessage() );
            $this->logApiCall( 'cart.delete.json', [], $e->getCode(), $this->account->getConfig(), null, null, null, $e->getMessage()  );
            return false;
        }
    }

    /**
     * @param $fields
     * @return array|false|mixed|null
     */
    public function addCart($fields)
    {
        $this->setApiKey();

        try{
            $result = $this->account->accountCartAdd(new AccountCartAdd($fields));
//            Log::debug( print_r($result,1) );
            $this->logApiCall( 'account.cart.add.json', $result->getResult(), $result->getReturnCode(), $this->cart->getConfig(), null, null, null, $result->getReturnMessage(), $fields  );

            if ($result->getReturnCode() == 0) {
                return $this->mapToArray($result);
            } else {
                if ($this->debug) {
                    Log::debug(print_r($result, 1));
                }

                return $this->mapToArray($result);
            }
        } catch (\Exception $e){

            Log::debug( $e->getMessage() );
            $this->logApiCall( 'account.cart.add.json', [], $e->getCode(), $this->account->getConfig(), null, null, null, $e->getMessage() , $fields );
            return false;
        }
    }

    /**
     * @param $store_id
     * @return array|mixed|null
     */
    public function getAbandonedCart($store_id)
    {
        $this->setApiKey();

        try{
            $this->cart->getConfig()->setApiKey('store_key', $store_id);
            $result = $this->order->orderAbandonedList(
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
                'force_all'
            );

            $this->logApiCall( 'order.abandoned.list.json', $result->getResult(), $result->getReturnCode(), $this->cart->getConfig(), null, null, null, $result->getReturnMessage() );

            if ($result->getReturnCode() == 0) {
                /**
                 * return object cause it cant be right maped to array...  swagger issue
                 */
                return $this->mapToArray($result->getResult()->getOrder());
//                return json_decode( $result->getResult()->__toString() , true, 512, JSON_OBJECT_AS_ARRAY) ;
            } else {
                if ($this->debug) {
                    Log::debug(print_r($result, 1));
                }

                return null;
            }
        } catch (\Exception $e){
//            Log::debug( $e->getMessage() );
            $this->logApiCall( 'order.abandoned.list.json', [], $e->getCode(), $this->account->getConfig(), null, null, null, $e->getMessage()  );

            return null;
        }
    }

    /**
     * @param null $store_id
     * @return false|int
     */
    public function getCategoryCount( $store_id=null )
    {
        $this->setApiKey();

        try{

            $this->order->getConfig()->setApiKey('store_key', $store_id);
            $result = $this->category->categoryCount();
            $this->logApiCall( 'category.count.json', $result->getResult(), $result->getReturnCode(), $this->order->getConfig(), null, null, null, $result->getReturnMessage()  );

            if ($result->getReturnCode() == 0) {
                return $result->getResult()->getCategoriesCount();
            } else {
                if ($this->debug) {
                    Log::debug(print_r($result, 1));
                }

                return false;
            }
        } catch (\Exception $e){
//            Log::debug( $e->getMessage() );
            $this->logApiCall( 'category.count.json', [], $e->getCode(), $this->account->getConfig(), null, null, null, $e->getMessage()  );
            return false;
        }
    }

    /**
     * @param null $store_id
     * @return array|false|mixed|null
     */
    public function getCategoryList( $store_id=null  )
    {
        $this->setApiKey();

        try{

            $this->order->getConfig()->setApiKey('store_key', $store_id);
            $result = $this->category->categoryList( null, null, null);
            $this->logApiCall( 'category.list.json', $result->getResult(), $result->getReturnCode(), $this->order->getConfig(), null, null, null, $result->getReturnMessage()  );

            if ($result->getReturnCode() == 0) {
                return $this->mapToArray($result);
            } else {
                if ($this->debug) {
                    Log::debug(print_r($result, 1));
                }

                return false;
            }
        } catch (\Exception $e){
//            Log::debug( $e->getMessage() );
            $this->logApiCall( 'category.list.json', [], $e->getCode(), $this->account->getConfig(), null, null, null, $e->getMessage()  );
            return false;
        }
    }

    public function getCategoryListPage( $store_id=null, $page_cursor=null  )
    {
        $this->setApiKey();

        try{

            $this->order->getConfig()->setApiKey('store_key', $store_id);
            $result = $this->category->categoryList( null, null, $page_cursor);
            $this->logApiCall( 'category.list.json', $result->getResult(), $result->getReturnCode(), $this->order->getConfig(), null, null, null, $result->getReturnMessage() ,['page_cursor' => $page_cursor] );

            if ($result->getReturnCode() == 0) {
                return $this->mapToArray($result);
            } else {
                if ($this->debug) {
                    Log::debug(print_r($result, 1));
                }

                return false;
            }
        } catch (\Exception $e){
//            Log::debug( $e->getMessage() );
            $this->logApiCall( 'category.list.json', [], $e->getCode(), $this->account->getConfig(), null, null, null, $e->getMessage() ,['page_cursor' => $page_cursor] );
            return false;
        }
    }

    /**
     * @param null $store_id
     * @param null $category_id
     * @return array|false|mixed|null
     */
    public function getCategoryInfo( $store_id=null, $category_id=null )
    {
        $this->setApiKey();

        try{

            $this->order->getConfig()->setApiKey('store_key', $store_id);
            $result = $this->category->categoryInfo($category_id);
            $this->logApiCall( 'category.info.json', $result->getResult(), $result->getReturnCode(), $this->order->getConfig(), null, null, null, $result->getReturnMessage() ,['category_id' => $category_id] );

            if ($result->getReturnCode() == 0) {
                return $this->mapToArray($result->getResult());
            } else {
                if ($this->debug) {
                    Log::debug(print_r($result, 1));
                }

                return false;
            }
        } catch (\Exception $e){
//            Log::debug( $e->getMessage() );
            $this->logApiCall( 'category.info.json', [], $e->getCode(), $this->account->getConfig(), null, null, null, $e->getMessage(), ['category_id' => $category_id]  );
            return false;
        }

    }

    /**
     * @param null $store_id
     * @param null $category_id
     * @param array $fields
     * @return array|false|mixed|null
     */
    public function updateCategory($store_id=null, $category_id=null, $fields=[])
    {
        $this->setApiKey();

        try{
            $this->order->getConfig()->setApiKey('store_key', $store_id);

            if ($fields) {
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

                $this->logApiCall( 'category.update.json', $result->getResult(), $result->getReturnCode(), $this->product->getConfig(), null, null, null, $result->getReturnMessage(), ['category_id' => $category_id, 'fields' => $fields ]  );

                if ($result->getReturnCode() == 0) {
                    return $this->getCategoryInfo($store_id, $category_id);
                } else {
                    if ($this->debug) {
                        Log::debug(print_r($result, 1));
                    }

                    return null;
                }
            } else {
                return null;
            }
        } catch (\Exception $e){
//            Log::debug( $e->getMessage() );
            $this->logApiCall( 'category.update.json', [], $e->getCode(), $this->account->getConfig(), null, null, null, $e->getMessage(), ['category_id' => $category_id, 'fields' => $fields ]   );
            return false;
        }

    }

    /**
     * @param null $store_id
     * @param null $category_id
     * @return bool
     */
    public function deleteCategory($store_id=null, $category_id=null)
    {
        $this->setApiKey();

        if ( !$store_id || !$category_id ) return false;

        try{
            $this->order->getConfig()->setApiKey('store_key', $store_id);
            $result = $this->category->categoryDelete( $category_id );
            $this->logApiCall( 'category.delete.json', $result->getResult(), $result->getReturnCode(), $this->product->getConfig(), null, null, null, $result->getReturnMessage() ,['category_id'=>$category_id] );

            if ($result->getReturnCode() == 0) {
                return true;
            } else {
                if ($this->debug) Log::debug(print_r($result, 1));
                return false;
            }
        } catch (\Exception $e){
//            Log::debug( $e->getMessage() );
            $this->logApiCall( 'category.delete.json', [], $e->getCode(), $this->account->getConfig(), null, null, null, $e->getMessage()  );
            return false;
        }

    }

    /**
     * @param null $store_id
     * @return false|int
     */
    public function getOrderCount( $store_id=null )
    {
        $this->setApiKey();

        try{

            $this->order->getConfig()->setApiKey('store_key', $store_id);
            $result = $this->order->orderCount();
            $this->logApiCall( 'order.count.json', $result->getResult(), $result->getReturnCode(), $this->order->getConfig(), null, null, null, $result->getReturnMessage()  );

            if ($result->getReturnCode() == 0) {
                return $result->getResult()->getOrdersCount();
            } else {
                if ($this->debug) Log::debug(print_r($result, 1));
                return false;
            }
        } catch (\Exception $e){
//            Log::debug( $e->getMessage() );
            $this->logApiCall( 'order.count.json', [], $e->getCode(), $this->account->getConfig(), null, null, null, $e->getMessage()  );
            return false;
        }
    }

    /**
     * @param null $store_id
     * @return array|false|mixed|null
     */
    public function getOrderStatuses( $store_id=null )
    {
        $this->setApiKey();

        try{

            $this->order->getConfig()->setApiKey('store_key', $store_id);
            $result = $this->order->orderStatusList();
            $this->logApiCall( 'order.status.list.json', $result->getResult(), $result->getReturnCode(), $this->order->getConfig(), null, null, null, $result->getReturnMessage()  );

            if ($result->getReturnCode() == 0) {
                return $this->mapToArray($result->getResult());
            } else {
                if ($this->debug) Log::debug(print_r($result, 1));
                return false;
            }
        } catch (\Exception $e){
//            Log::debug( $e->getMessage() );
            $this->logApiCall( 'order.status.list.json', [], $e->getCode(), $this->account->getConfig(), null, null, null, $e->getMessage()  );
            return false;
        }
    }

    /**
     * @param null $store_id
     * @param null $sort_by
     * @param null $sort_direct
     * @param int $limit
     * @param null $created_from
     * @return array|false|mixed|null
     */
    public function getOrderList( $store_id=null , $sort_by=null, $sort_direct=null, $limit=10, $created_from=null )
    {
        $this->setApiKey();

        try{

            $this->order->getConfig()->setApiKey('store_key', $store_id);
            $result = $this->order->orderList(
                null,
                null,
                null,
                null,
                null,
                null,
                $limit,
                null,
                $sort_by,
                $sort_direct,
                'force_all',
                null,
                null,
                $created_from,
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

            $this->logApiCall( 'order.list.json', $result->getResult(), $result->getReturnCode(), $this->order->getConfig(), null, null, null, $result->getReturnMessage(), [ 'sort_by' => $sort_by, 'sort_direct' => $sort_direct, 'limit' => $limit, 'created_from' => $created_from]  );

            if ($result->getReturnCode() == 0) {
                return $this->mapToArray($result);
            } else {
                if ($this->debug) Log::debug(print_r($result, 1));
                return false;
            }
        } catch (\Exception $e){
            Log::debug( $e->getMessage() );
            $this->logApiCall( 'order.list.json', [], $e->getCode(), $this->account->getConfig(), null, null, null, $e->getMessage(), [ 'sort_by' => $sort_by, 'sort_direct' => $sort_direct, 'limit' => $limit, 'created_from' => $created_from]  );
            return false;
        }
    }

    /**
     * @param null $store_id
     * @param null $page_cursor
     * @return array|false|mixed|null
     */
    public function getOrderListPage( $store_id=null, $page_cursor=null )
    {
        $this->setApiKey();

        try{

            $this->order->getConfig()->setApiKey('store_key', $store_id);
            $result = $this->order->orderList(
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                $page_cursor,
                null,
                null,
                'force_all',
                null
            );

            $this->logApiCall( 'order.list.json', $result->getResult(), $result->getReturnCode(), $this->order->getConfig(), null, null, null, $result->getReturnMessage() ,['page_cursor' => $page_cursor] );

            if ($result->getReturnCode() == 0) {
                return $this->mapToArray($result);
            } else {
                if ($this->debug) Log::debug(print_r($result, 1));
                return false;
            }
        } catch (\Exception $e){
//            Log::debug( $e->getMessage() );
            $this->logApiCall( 'order.list.json', [], $e->getCode(), $this->account->getConfig(), null, null, null, $e->getMessage(), ['page_cursor'=>$page_cursor]  );
            return false;
        }

    }

    /**
     * @param null $store_id
     * @param null $order_id
     * @return array|false|mixed|null
     */
    public function getOrderInfo( $store_id=null, $order_id=null )
    {
        $this->setApiKey();

        try{

            $this->order->getConfig()->setApiKey('store_key', $store_id);
            $result = $this->order->orderInfo( $order_id ,'force_all');
            $this->logApiCall( 'order.info.json', $result->getResult(), $result->getReturnCode(), $this->order->getConfig(), null, null, null, $result->getReturnMessage() ,['order_id'=>$order_id] );

            if ($result->getReturnCode() == 0) {
                return $this->mapToArray($result->getResult());
            } else {
                if ($this->debug) Log::debug(print_r($result, 1));
                return false;
            }
        } catch (\Exception $e){
//            Log::debug( $e->getMessage() );
            $this->logApiCall( 'order.info.json', [], $e->getCode(), $this->account->getConfig(), null, null, null, $e->getMessage() ,['order_id'=>$order_id] );
            return false;
        }
    }

    /**
     * @param null $store_id
     * @param null $fields
     * @return array|false
     */
    public function createOrder( $store_id=null, $fields=null )
    {
        $this->setApiKey();

        try{

            $this->order->getConfig()->setApiKey('store_key', $store_id);
            $result = $this->order->orderAdd($fields);
            $this->logApiCall( 'order.add.json', $result->getResult(), $result->getReturnCode(), $this->order->getConfig(), null, null, null, $result->getReturnMessage() , $fields );

            if ($result->getReturnCode() == 0) {
                return [0, $this->mapToArray($result->getResult())];
            } else {
                if ($this->debug) Log::debug(print_r($result, 1));
                return [$result->getReturnCode(), $result->getReturnMessage()];
            }

        } catch (\Exception $e){
            Log::debug( $e->getMessage() );
            $this->logApiCall( 'order.add.json', [], $e->getCode(), $this->account->getConfig(), null, null, null, $e->getMessage() , $fields );
            return false;
        }
    }

    /**
     * @param null $store_id
     * @return false|int
     */
    public function getProductCount($store_id=null)
    {
        $this->setApiKey();

        try{
            $this->order->getConfig()->setApiKey('store_key', $store_id);
            $result = $this->product->productCount();
            $this->logApiCall( 'product.count.json', $result->getResult(), $result->getReturnCode(), $this->product->getConfig(), null, null, null, $result->getReturnMessage()  );

            if ($result->getReturnCode() == 0) {
                return $result->getResult()->getProductsCount();
            } else {
                if ($this->debug) {
                    Log::debug(print_r($result, 1));
                }

                return false;
            }
        } catch (\Exception $e){
//            Log::debug( $e->getMessage() );
            $this->logApiCall( 'product.count.json', [], $e->getCode(), $this->account->getConfig(), null, null, null, $e->getMessage()  );
            return false;
        }

    }

    /**
     * @param null $store_id
     * @param null $ids
     * @param null $sort_by
     * @param null $sort_direct
     * @param null $limit
     * @param null $created_from
     * @return array|false|mixed|null
     */
    public function getProductList($store_id=null, $ids=null, $sort_by=null, $sort_direct=null, $limit=null, $created_from=null )
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

            $result = $this->product->productList(
                null,
                null,
                $limit,
                'id,name,url,description,price,categories_ids,images,u_sku,type,create_at,modify_at,quantity',
                null,
                null,
                $created_from,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                $ids,
                null,
                null,
                null,
                $sort_by,
                $sort_direct
            );

            $this->logApiCall( 'product.list.json', $result->getResult(), $result->getReturnCode(), $this->product->getConfig(), null, null, null, $result->getReturnMessage() ,['ids' => $ids ] );

            if ($result->getReturnCode() == 0) {
                return $this->mapToArray($result);
            } else {
                if ($this->debug) Log::debug(print_r($result, 1));
                return false;
            }
        } catch (\Exception $e){
//            Log::debug( $e->getMessage() );
            $this->logApiCall( 'product.list.json', [], $e->getCode(), $this->account->getConfig(), null, null, null, $e->getMessage() ,['ids' => $ids ]  );
            return false;
        }
    }

    /**
     * @param null $store_id
     * @param null $page_cursor
     * @return array|false|mixed|null
     */
    public function getProductListPage( $store_id=null, $page_cursor=null )
    {
        $this->setApiKey();

        try{

            $this->order->getConfig()->setApiKey('store_key', $store_id);
            $result = $this->product->productList(
                $page_cursor,
                null,
                null,
                'id,name,url,description,price,categories_ids,images,u_sku,type,create_at,modify_at,quantity',
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
                null,
                null,
                null,
                null  );

            $this->logApiCall( 'product.list.json', $result->getResult(), $result->getReturnCode(), $this->product->getConfig(), null, null, null, $result->getReturnMessage() ,['page_cursor' => $page_cursor] );

            if ($result->getReturnCode() == 0) {
                return $this->mapToArray($result);
            } else {
                if ($this->debug) Log::debug(print_r($result, 1));
                return false;
            }
        } catch (\Exception $e){
//            Log::debug( $e->getMessage() );
            $this->logApiCall( 'product.list.json', [], $e->getCode(), $this->account->getConfig(), null, null, null, $e->getMessage(), ['page_cursor'=>$page_cursor] );
            return false;
        }
    }

    /**
     * @param null $store_id
     * @param null $product_id
     * @return array|false|mixed|null
     */
    public function getProductInfo($store_id=null, $product_id=null)
    {
        $this->setApiKey();

        try{

            $this->order->getConfig()->setApiKey('store_key', $store_id);
            $result = $this->product->productInfo($product_id,'force_all');
            $this->logApiCall( 'product.info.json', $result->getResult(), $result->getReturnCode(), $this->product->getConfig(), null, null, null, $result->getReturnMessage() ,['product_id'=>$product_id] );

            if ($result->getReturnCode() == 0) {
                return $this->mapToArray($result->getResult());
            } else {
                if ($this->debug) Log::debug(print_r($result, 1));
                return null;
            }
        } catch (\Exception $e){
//            Log::debug( $e->getMessage() );
            $this->logApiCall( 'product.info.json', [], $e->getCode(), $this->account->getConfig(), null, null, null, $e->getMessage(), ['product_id'=>$product_id]  );
            return false;
        }

    }

    /**
     * @param null $store_id
     * @param null $product_id
     * @param array $fields
     * @return array|false|mixed|null
     */
    public function updateProduct($store_id=null, $product_id=null, $fields=[])
    {
        $this->setApiKey();

        try{

            $this->order->getConfig()->setApiKey('store_key', $store_id);

            if ($fields) {
                $result = $this->product->productUpdate(
                    $product_id,
                    null,
                    (isset($fields['price'])) ? $fields['price'] : null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    (isset($fields['quantity'])) ? $fields['quantity'] : null,
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

                $this->logApiCall( 'product.update.json', $result->getResult(), $result->getReturnCode(), $this->product->getConfig(), null, null, null, $result->getReturnMessage(), ['product_id' => $product_id, 'fields' => $fields]  );

                if ($result->getReturnCode() == 0) {
                    return $this->getProductInfo($store_id, $product_id);
                } else {
                    if ($this->debug) {
                        Log::debug(print_r($result, 1));
                    }

                    return null;
                }

            } else {
                return null;
            }
        } catch (\Exception $e){
            $this->logApiCall( 'product.update.json', [], $e->getCode(), $this->account->getConfig(), null, null, null, $e->getMessage(), ['product_id' => $product_id, 'fields' => $fields]  );
            return false;
        }
    }

    /**
     * @param null $store_id
     * @param array $fields
     * @return array|false|null
     */
    public function addProduct( $store_id=null, $fields=[] ){
        $this->setApiKey();

        try{
            $this->order->getConfig()->setApiKey('store_key', $store_id);

            if ($fields) {
                $result = $this->product->productAdd($fields);
                $this->logApiCall( 'product.add.json', $result->getResult(), $result->getReturnCode(), $this->product->getConfig(), null, null, null, $result->getReturnMessage(), $fields  );

                if ($result->getReturnCode() == 0) {
                    return [0, $result];
                } else {
                    if ($this->debug) {
                        Log::debug(print_r($result, 1));
                    }

                    return [$result->getReturnCode(), $result->getReturnMessage()];
                }
            } else {
                return null;
            }
        } catch (\Exception $e){
            Log::debug($e->getMessage());
            $this->logApiCall( 'product.add.json', [], $e->getCode(), $this->account->getConfig(), null, null, null, $e->getMessage(), $fields  );
            return false;
        }
    }

    /**
     * @param null $store_id
     * @param null $product_id
     * @return array|false|mixed|null
     */
    public function getProductVariants($store_id=null, $product_id=null)
    {
        $this->setApiKey();

        try{
            $this->order->getConfig()->setApiKey('store_key', $store_id);
            $result = $this->product->productChildItemList(
                null,
                0,
                100,
                'force_all',
                null,
                null,
                null,
                null,
                null,
                $product_id
            );

            $this->logApiCall( 'product.child_item.list.json', $result->getResult(), $result->getReturnCode(), $this->product->getConfig(), null, null, null, $result->getReturnMessage() ,['product_id'=>$product_id] );

            if ($result->getReturnCode() == 0) {
                return $this->mapToArray($result->getResult());
            } else {
                if ($this->debug) {
                    Log::debug(print_r($result, 1));
                }

                return null;
            }
        } catch (\Exception $e){
//            Log::debug( $e->getMessage() );
            $this->logApiCall( 'product.child_item.list.json', [], $e->getCode(), $this->account->getConfig(), null, null, null, $e->getMessage(), ['product_id'=>$product_id]  );
            return false;
        }

    }

    /**
     * @param null $store_id
     * @param null $product_id
     * @param null $id
     * @param $fields
     * @return array|false|mixed|null
     */
    public function updateProductVariant($store_id=null, $product_id=null, $id=null, $fields)
    {
        $this->setApiKey();

        try{

            $this->order->getConfig()->setApiKey('store_key', $store_id);

            $result = $this->product->productVariantUpdate(
                $id,
                $product_id,
                null,
                null,
                null,
                null,
                null,
                null,
                (isset($fields['default_price'])) ? $fields['default_price'] : null,
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

            $this->logApiCall( 'product.variant.update.json', $result->getResult(), $result->getReturnCode(), $this->product->getConfig(), null, null, null, $result->getReturnMessage() ,['product_id'=>$product_id, 'variant_id' => $id, 'fields' => $fields] );

            if ($result->getReturnCode() == 0) {
                return $this->mapToArray($result->getResult());
            } else {
                if ($this->debug) {
                    Log::debug(print_r($result, 1));
                }

                return null;
            }
        } catch (\Exception $e){
            $this->logApiCall( 'product.variant.update.json', [], $e->getCode(), $this->account->getConfig(), null, null, null, $e->getMessage(), ['product_id'=>$product_id, 'variant_id' => $id, 'fields' => $fields]  );
            return false;
        }
    }

    /**
     * @param null $store_id
     * @param null $product_id
     * @return bool
     */
    public function deleteProduct($store_id=null, $product_id=null)
    {
        $this->setApiKey();

        if (!$store_id || !$product_id) {
            return false;
        }

        try{
            $this->order->getConfig()->setApiKey('store_key', $store_id);
            $result = $this->product->productDelete( $product_id );
            $this->logApiCall( 'product.delete.json', $result->getResult(), $result->getReturnCode(), $this->product->getConfig(), null, null, null, $result->getReturnMessage() , ['product_id'=>$product_id] );

            if ($result->getReturnCode() == 0) {
                return true;
            } else {
                if ($this->debug) {
                    Log::debug(print_r($result, 1));
                }
                return false;
            }
        } catch (\Exception $e){
//            Log::debug( $e->getMessage() );
            $this->logApiCall( 'product.delete.json', [], $e->getCode(), $this->account->getConfig(), null, null, null, $e->getMessage() ,['product_id'=>$product_id] );
            return false;
        }

    }

    /**
     * @param null $store_id
     * @param null $product_id
     * @param null $image_id
     * @return false
     */
    public function deleteProductImage($store_id=null, $product_id=null, $image_id=null)
    {
        $this->setApiKey();

        if (!$store_id || !$product_id || !$image_id) {
            return false;
        }

        try{
            $this->order->getConfig()->setApiKey('store_key', $store_id);
            $product    = $this->product->productInfo($product_id,'force_all')->getResult();
            $images     = $product->getImages();


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

    /**
     * @param null $store_id
     * @param null $product_id
     * @param array $fields
     * @return ApiClient\Model\InlineResponse20037|false|null
     */
    public function addProductImage($store_id=null, $product_id=null, $fields=[])
    {
        $this->setApiKey();

        try{

            $this->order->getConfig()->setApiKey('store_key', $store_id);

            if ($fields) {
                $result = $this->product->productImageAdd(
                    $product_id,
                    $fields['image_name'],
                    $fields['type'],
                    $fields['url'],
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null
                );

                $this->logApiCall( 'product.image.add.json', $result->getResult(), $result->getReturnCode(), $this->product->getConfig(), null, null, null, $result->getReturnMessage(), array_merge(['product_id'=>$product_id],$fields)  );

                if ($result->getReturnCode() == 0) {
                    return $result;
                } else {
                    if ($this->debug) {
                        Log::debug(print_r($result, 1));
                    }

                    return null;
                }
            } else {
                return null;
            }
        } catch (\Exception $e){
            Log::debug( $e->getMessage() );
            $this->logApiCall( 'product.image.add.json', [], $e->getCode(), $this->account->getConfig(), null, null, null, $e->getMessage(), array_merge(['product_id'=>$product_id],$fields)  );

            return false;
        }
    }

    /**
     * @param null $store_id
     * @return false|int
     */
    public function getCustomerCount($store_id=null)
    {
        $this->setApiKey();

        try{
            $this->order->getConfig()->setApiKey('store_key', $store_id);
            $result = $this->customer->customerCount();
            $this->logApiCall( 'customer.count.json', $result->getResult(), $result->getReturnCode(), $this->customer->getConfig(), null, null, null, $result->getReturnMessage()  );

            if ($result->getReturnCode() == 0) {
                return $result->getResult()->getCustomersCount();
            } else {
                if ($this->debug) {
                    Log::debug(print_r($result, 1));
                }

                return false;
            }

        } catch (\Exception $e){
//            Log::debug( $e->getMessage() );
            $this->logApiCall( 'customer.count.json', [], $e->getCode(), $this->account->getConfig(), null, null, null, $e->getMessage()  );
            return false;
        }

    }

    /**
     * @param null $store_id
     * @param null $customer_id
     * @return array|false|mixed|null
     */
    public function getCustomer($store_id=null, $customer_id=null)
    {
        $this->setApiKey();

        try{
            $this->order->getConfig()->setApiKey('store_key', $store_id);
            $result = $this->customer->customerInfo( $customer_id, "force_all", null, null );
            $this->logApiCall( 'customer.info.json', $result->getResult(), $result->getReturnCode(), $this->customer->getConfig(), null, null, null, $result->getReturnMessage(), ['id' => $customer_id ]  );

            if ($result->getReturnCode() == 0) {
                return $this->mapToArray($result->getResult());
            } else {
                if ($this->debug) {
                    Log::debug(print_r($result, 1));
                }

                return false;
            }

        } catch (\Exception $e){
            Log::debug( $e->getMessage() );
            $this->logApiCall( 'customer.info.json', [], $e->getCode(), $this->account->getConfig(), null, null, null, $e->getMessage() , ['id' => $customer_id ]  );
            return false;
        }

    }

    /**
     * @param null $store_id
     * @return array|false|mixed|null
     */
    public function getCustomerList($store_id=null)
    {
        $this->setApiKey();

        try{
            $this->order->getConfig()->setApiKey('store_key', $store_id);
            $result = $this->customer->customerList( null, null, null, null,null,null,null,'force_all', null,  null, null, null);
            $this->logApiCall( 'customer.list.json', $result->getResult(), $result->getReturnCode(), $this->customer->getConfig(), null, null, null, $result->getReturnMessage()  );

            if ($result->getReturnCode() == 0) {
                return $this->mapToArray($result);
            } else {
                if ($this->debug) {
                    Log::debug(print_r($result, 1));
                }

                return false;
            }
        } catch (\Exception $e){
//            Log::debug( $e->getMessage() );
            $this->logApiCall( 'customer.list.json', [], $e->getCode(), $this->account->getConfig(), null, null, null, $e->getMessage()  );
            return false;
        }
    }

    /**
     * @param null $store_id
     * @return array|false|mixed|null
     */
    public function getSubscriberList($store_id=null)
    {
        $this->setApiKey();

        try{

            $this->order->getConfig()->setApiKey('store_key', $store_id);
            $result = $this->subscriber->subscriberList(
                null,
                null,
                null,
                null,
                null,
                null,
                null
            );

            $this->logApiCall( '/subscriber.list.json', $result->getResult(), $result->getReturnCode(), $this->customer->getConfig(), null, null, null, $result->getReturnMessage()  );

            if ($result->getReturnCode() == 0) {
                return $this->mapToArray($result);
            } else {
                if ($this->debug) {
                    Log::debug(print_r($result, 1));
                }

                return false;
            }
        } catch (\Exception $e){
//            Log::debug( $e->getMessage() );
            $this->logApiCall( '/subscriber.list.json', [], $e->getCode(), $this->account->getConfig(), null, null, null, $e->getMessage()  );
            return false;
        }
    }

    /**
     * @param null $store_id
     * @param null $page_cursor
     * @return array|false|mixed|null
     */
    public function getCustomerListPage( $store_id=null, $page_cursor=null )
    {
        $this->setApiKey();

        try{

            $this->order->getConfig()->setApiKey('store_key', $store_id);
            $result = $this->customer->customerList( $page_cursor, null, null, null, null, null, null, 'force_all', null, null, null, null );
            $this->logApiCall( 'customer.list.json', $result->getResult(), $result->getReturnCode(), $this->customer->getConfig(), null, null, null, $result->getReturnMessage() , ['page_cursor' => $page_cursor] );

            if ($result->getReturnCode() == 0) {
                return $this->mapToArray($result);
            } else {
                if ($this->debug) {
                    Log::debug(print_r($result, 1));
                }

                return false;
            }
        } catch (\Exception $e){
//            Log::debug( $e->getMessage() );
            $this->logApiCall( 'customer.list.json', [], $e->getCode(), $this->account->getConfig(), null, null, null, $e->getMessage(), ['page_cursor' => $page_cursor]  );

            return false;
        }
    }

    /**
     * @param null $data
     * @return array|mixed|null
     */
    private function mapToArray($data=null)
    {
        if ($data == null) return null;

        if ( is_array($data) ){
            return array_map(function($item){

                try {
                    return json_decode($item->__toString(), true, 512, JSON_OBJECT_AS_ARRAY);
                } catch (\Exception $e) {
                    return $item;
                }
            }, $data);
        }

        if ( is_object($data) ){
            try {
                return json_decode($data->__toString(), true, 512, JSON_OBJECT_AS_ARRAY);
            } catch (\Exception $e) {
                return $data;
            }
        }
    }

    /**
     * @param null|string       $action    Action name
     * @param mixed             $result    Result of response
     * @param string|int|null   $code      Code of response
     * @param array|object|null $config    Config
     * @param string|null       $store_id  Store ID
     * @param string|null       $store_url Store Url
     * @param string|null       $user_id   User ID
     * @param string|null       $msg       Message
     * @param array|null        $params    Parameters
     *
     * @return void
     */
    private function logApiCall($action = null, $result = [], $code = null, $config = null, $store_id = null, $store_url = null, $user_id = null, $msg = null, $params = null)
    {
        // for Unittest do not log
        if ($this->isTest) {
            return;
        }

        if (is_object($config)) {
            $p = ['api_key' => $config->getApiKey('api_key'), 'store_key' => $config->getApiKey('store_key')];
        } else {
            $p = [];
        }

        if (is_array($params)) {
            foreach ($params as $k => $v) {
                $p[$k] = $v;
            }
        }

        if ($msg) {
            $p['msg'] = $msg;
        }

        $log = Logger::create([
            'action'    => $action,
            'code'      => $code,
            'params'    => $p,
            'response'  => !empty($result) ? json_encode($this->mapToArray($result)) : '',
            'store_id'  => ($store_id) ? $store_id : $config->getApiKey('store_key'),
            'store_url' => $store_url,
            'user_id'   => $user_id
        ]);

        $this->log->push( $log );
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * @return false
     */
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
