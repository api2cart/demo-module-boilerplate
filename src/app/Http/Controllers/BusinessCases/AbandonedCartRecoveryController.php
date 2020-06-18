<?php

namespace App\Http\Controllers\BusinessCases;

use App\Http\Controllers\Controller;
use App\Mail\AbandonedCartRecovery;
use App\Services\Api2Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class AbandonedCartRecoveryController extends CasesController
{

    private $api2cart;

    public function __construct(Api2Cart $api2Cart)
    {
        $this->api2cart = $api2Cart;
    }

    public function index()
    {
//        dd( $this->api2cart->getAbandonedCart('1316ad9a66ac871ce46a3d59005acc9c') );
        return view('business_cases.abandoned_cart_recovery.index');
    }

    public function send(Request $request)
    {

        $abandoned = [];

        foreach ($request->get('items') as $item){
            $pid = explode(':', $item);
            $cart = collect( $this->api2cart->getAbandonedCart( $pid[0] ) );
            $storeInfo = $this->api2cart->getCart( $pid[0] );

            // adding selected abandoned carts
            foreach ($cart as $ci){
                if ( isset($ci['customer']['id']) && $ci['customer']['id'] == $pid[1] ) {
                    $ci['store_info'] = $storeInfo;
                    $ci['store_key'] = $pid[0];
                    $abandoned[] = $ci;
                }
            }
        }

        foreach ($abandoned as $k=>$cart){

            foreach ($cart['order_products'] as $op){
                $abandoned[$k]['products'][] = $this->api2cart->getProductInfo( $cart['store_key'], $op['product_id']);
            }

            Mail::to( $request->get('email') )->send( new AbandonedCartRecovery( $abandoned[$k] ) );

        }


    }

    public function compose(Request $request)
    {
//        Log::debug( $request->all() );

        $abandoned = [];

        foreach ($request->get('items') as $item){
            $pid = explode(':', $item);
            $cart = collect( $this->api2cart->getAbandonedCart( $pid[0] ) );
            $storeInfo = $this->api2cart->getCart( $pid[0] );

            // adding selected abandoned carts
            foreach ($cart as $ci){
                if ( isset($ci['customer']['id']) && $ci['customer']['id'] == $pid[1] ) {
                    $ci['store_info'] = $storeInfo;
                    $ci['store_key'] = $pid[0];
                    $abandoned[] = $ci;
                }
            }
        }


            foreach ($abandoned[0]['order_products'] as $op){
                $abandoned[0]['products'][] = $this->api2cart->getProductInfo( $abandoned[0]['store_key'], $op['product_id']);
            }

//        Log::debug( print_r($abandoned[0],1) );

            $data = $abandoned[0];

        return view('emails.abandoned', compact('data') );


    }

}
