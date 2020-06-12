<?php

namespace App\Http\Controllers\BusinessCases;

use App\Http\Controllers\Controller;
use App\Services\Api2Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\AutomaticEmailSending;

class AutomaticEmailSendingController extends CasesController
{

    private $api2cart;

    public function __construct(Api2Cart $api2Cart)
    {
        $this->api2cart = $api2Cart;
    }



    public function index()
    {
        return view('business_cases.automatic_email_sending.index');
    }

    public function compose(Request $request)
    {

        $products = collect();

        // load selected products
        foreach ($request->get('products') as $item){
            $pid = explode(':', $item);
            $storeInfo = $this->api2cart->getCart( $pid[0] );

            $res = $this->api2cart->getProductInfo($pid[0], $pid[1]);
            $res['currency'] = ( isset($storeInfo['stores_info'][0]['currency']) ) ? $storeInfo['stores_info'][0]['currency'] : '';

            $products->push( $res );
        }

//        Log::debug( print_r($products,1) );

        return view('emails.products', compact('products'));
    }

    public function send(Request $request)
    {
        $products = collect();

        // load selected products
        foreach ($request->get('products') as $item){
            $pid = explode(':', $item);
            $storeInfo = $this->api2cart->getCart( $pid[0] );

            $res = $this->api2cart->getProductInfo($pid[0], $pid[1]);
            $res['currency'] = ( isset($storeInfo['stores_info'][0]['currency']) ) ? $storeInfo['stores_info'][0]['currency'] : '';

            $products->push( $res );
        }


        Mail::to( $request->get('email') )->send(new AutomaticEmailSending($products));

        return response()->json(['ok']);

    }



}
