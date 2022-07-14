<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRequest;
use App\Services\Api2Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Cache;

class StoresController extends Controller
{
    const METHOD_DB           = '_method_db_';

    private $api2cart;


    public function __construct(Api2Cart $api2Cart)
    {
        $this->api2cart = $api2Cart;
    }

    public function index()
    {

//        $this->api2cart->test();

        return view('stores.index');
    }


    public function storeList(Request $request)
    {
        \Debugbar::disable();

        /**
         * get account carts & avialable carts
         */
        $carts = collect($this->api2cart->getCartList());

        $allCarts = Cache::remember('allCarts', 3600, function () {
            return collect($this->api2cart->getCartsList());
        });

        $result = $carts->map(function ($store) use ($allCarts) {
            $info = Cache::remember('cart_' . $store['store_key'], 3600, function () use ($store) {
                return $this->api2cart->getCart( $store['store_key'] );
            });

            // put additional info
            $store['stores_info']['store_owner_info']   = [
                'owner' => ( isset($info['stores_info'][0]['store_owner_info']['owner']) ) ? $info['stores_info'][0]['store_owner_info']['owner'] : null,
                'email' => ( isset($info['stores_info'][0]['store_owner_info']['email']) ) ? $info['stores_info'][0]['store_owner_info']['email'] : null
            ];
            $store['cart_info']     = $allCarts->where('cart_id', $store['cart_id'])->first();

            return $store;
        });

        $data = [
            "recordsTotal"      => $result->count(),
            "recordsFiltered"   => $result->count(),
            "start"             => 0,
            "data"              => $result,

            'log'               => $this->api2cart->getLog(),
        ];

        return response()->json($data);
    }

    public function fields(Request $request, $id=null)
    {
        $store = collect($this->api2cart->getCartsList())->where('cart_id',$id)->first();
        $store['db'] = false;

        if (isset($store['params']['required'])) {
            foreach ($store['params']['required'] as $paramsSetKey => $params) {
                foreach ($params as $paramKey => $param) {
                    if (in_array($param['name'], ['store_url',])) {
                        unset($store['params']['required'][$paramsSetKey][$paramKey]);
                    }
                }
            }
        }

        if (isset($store['params']['additional'])) {
            foreach ($store['params']['additional'] as $key => $addParam) {
                if (strpos($addParam['name'], 'ftp_') === 0 ||
                    in_array($addParam['name'], ['cart_id', 'store_key', 'verify', 'db_tables_prefix'])
                ) {
                    unset($store['params']['additional'][$key]);
                }
            }
        }

        if (isset($store['cart_method']) && $store['cart_method'] === self::METHOD_DB) {
            $store['db'] = true;
        }

        return view('stores.store_fields', compact('store'));

    }


    public function create(Request $request)
    {
        // get supported carts
        $stores = collect($this->api2cart->getCartsList());

        if ( $request->ajax() ){
            return response()->json( ['data' => view('stores.form', compact('stores'))->render(), 'item' => $stores ] );
        }
        return redirect( route('stores.index') );
    }


    public function store(StoreRequest $request)
    {
        $requestData = $request->except(['_token']);
        $requestData['field']['cart_id'] = $request->get('cart_id');

        if (isset($requestData['field']['multicred'])) {
            $credFields = [];
            $filledSet = null;

            foreach ($requestData['field']['multicred'] as $key => $fields) {
                if ($filledSet !== null) {
                    continue;
                }

                $filterFields = array_filter($fields);

                if (count($fields) === count($filterFields)) {
                    $filledSet = $key;

                    foreach ($fields as $fieldName => $fieldValue) {
                        if ($fieldValue !== null) {
                            $credFields[$fieldName] = $fieldValue;
                        }
                    }
                }
            }

            unset($requestData['field']['multicred']);
            $credFields = array_merge($credFields, $requestData['field']);

            $fields = (isset($requestData['custom'])) ? array_merge($credFields, $requestData['custom']) : $credFields;
        } else {
            $fields = (isset($requestData['custom'])) ? array_merge($requestData['field'], $requestData['custom']) : $requestData['field'];
        }

        $result = $this->api2cart->addCart( $fields );


        if ( $request->ajax() ){

            Log::debug( print_r($result,1));

            if ( intval($result['return_code']) == 0 )  {
                return response()->json( ['data' => $result, 'log' => $this->api2cart->getLog() ] );
            } else {
                return response()->json( ['msg' => $result['return_message'], 'log' => $this->api2cart->getLog() ] , 404 );
            }
        }

        return redirect( route('stores.index') );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy($id=null)
    {
        if ( $this->api2cart->deleteCart( $id ) ){
            return response()->json([ 'log' => $this->api2cart->getLog() ]);
        } else {
            return response()->json([ 'log' => $this->api2cart->getLog() ], 404);
        }

    }

}
