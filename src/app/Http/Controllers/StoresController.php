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

        $stores = ($carts['result']['carts_count']) ? collect( $carts['result']['carts'] ) : collect([]);

        if ( $search ){

            $stores = $stores->filter(function($value, $key) use( $search ){
                // add more search fields if needed
                $a1 = ( stripos( $value['cart_id'], $search ) !== false  ) ? true : false;
                $a2 = ( stripos( $value['url'], $search ) !== false ) ? true : false;
                return ( $a1 || $a2);
            });

        }

        $perPage = ($request->get('length')) ? $request->get('length') : 10;
        $totalPages = $stores->count() / $perPage;
        $currPage   = $request->get('start') ? ($request->get('start')/$perPage)+1 : 1;


        $chunk = array_merge($stores->forPage($currPage,$perPage)->toArray());

        $data = [
            "recordsTotal"      => $carts['result']['carts_count'],
            "recordsFiltered"   => $stores->count(),
            "start"             => 0,
            "length"            => $perPage,
            "data"              => $chunk

        ];

        return response()->json($data);
    }

    public function storeDetails(Request $request, $id=null)
    {

    }
}
