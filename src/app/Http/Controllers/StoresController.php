<?php

namespace App\Http\Controllers;

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

        if ( !$carts->count() || !$allCarts->count() ) return response()->json([],404);

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
}
