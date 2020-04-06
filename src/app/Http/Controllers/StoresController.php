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

        $search = ($request->get('search')) ? $request->get('search')['value'] : null;
        $carts = $this->api2cart->getCartList();

        $allCarts = collect( $this->api2cart->getCartsList()['result']['supported_carts'] );



        $stores = ($carts['result']['carts_count']) ? collect( $carts['result']['carts'] ) : collect([]);

        $perPage = ($request->get('length')) ? $request->get('length') : 100;
        $totalPages = $stores->count() / $perPage;


        $stores = $stores->map(function ($store) use ($allCarts) {
            $info = $this->api2cart->getCart( $store['store_key'] )['result']['stores_info'][0];
            $store['stores_info']   = $info;
            $store['cart_info']     = $allCarts->where('cart_id', $store['cart_id'])->first();
            return $store;
        });



        $data = [
            "recordsTotal"      => $stores->count(),
            "recordsFiltered"   => $stores->count(),
            "start"             => 0,
            "length"            => $perPage,
            "data"              => $stores

        ];

        return response()->json($data);
    }

    public function storeDetails(Request $request, $id=null)
    {

    }
}
