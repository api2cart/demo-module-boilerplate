<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRequest;
use App\Services\Api2Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StoresController extends Controller
{
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
        $allCarts = collect($this->api2cart->getCartsList());

//        if ( !$carts->count() || !$allCarts->count() ) return response()->json([],404);


        $result = $carts->map(function ($store) use ($allCarts) {
            $info = $this->api2cart->getCart( $store['store_key'] );
            // put additional info
            $store['stores_info']['store_owner_info']   = [
                'owner' => ( isset($info['stores_info'][0]['store_owner_info']) ) ? $info['stores_info'][0]['store_owner_info']->getOwner() : null,
                'email' => ( isset($info['stores_info'][0]['store_owner_info']) ) ? $info['stores_info'][0]['store_owner_info']->getEmail() : null
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

    public function storeDetails(Request $request, $id=null)
    {

    }


    public function create(Request $request)
    {
        // get supported carts
        $stores = collect($this->api2cart->getCartsList());
//            ->whereIn('cart_id',['Amazon']);


        Log::debug( print_r($stores,1) );



        if ( $request->ajax() ){
            return response()->json( ['data' => view('stores.form', compact('stores'))->render(), 'item' => $stores ] );
        }
        return redirect( route('stores.index') );
    }


    public function store(StoreRequest $request)
    {
        Log::debug( $request->all() );

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
