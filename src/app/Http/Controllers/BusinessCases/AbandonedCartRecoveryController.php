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
        return view('business_cases.abandoned_cart_recovery.index');
    }

    public function send(Request $request)
    {

        $abandoneds = [];
        $countMail = 0;

        foreach ($request->get('items') as $item) {
            $pid = explode(':', $item);
            $cart = collect($this->api2cart->getAbandonedCart($pid[0]));
            $storeInfo = $this->api2cart->getCart($pid[0]);

            // adding selected abandoned carts
            foreach ($cart as $ci) {
                if (isset($ci['customer']['id']) && $ci['id'] == $pid[1] && !empty($ci['customer']['email'])) {
                    $ci['store_info'] = $storeInfo;
                    $ci['store_key'] = $pid[0];
                    $abandoneds[($pid[2] ?? $ci['customer']['email'] ?? $pid[0] . $pid[1])][] = $ci;
                }
            }
        }

        foreach ($abandoneds as $abandoned) {
            foreach ($abandoned as $k => $cart) {
                foreach ($cart['order_products'] as $op) {
                    if ($productInfo = $this->api2cart->getProductInfo($cart['store_key'], $op['product_id'])) {
                        $abandoned[$k]['products'][] = $productInfo;
                    }
                }

                if (isset($cart['customer']['email'])) {
                    try {
                        Mail::to($cart['customer']['email'])->send(new AbandonedCartRecovery([$abandoned[$k]]));
                        $countMail++;
                    } catch (Throwable $e) {
                        return response()->json([ 'log' => $this->api2cart->getLog(), 'success' => false, 'errormessage' => $e->getMessage()]);
                    }
                }
            }
        }

        return response()->json([ 'log' => $this->api2cart->getLog(), 'success' => true, 'countMail' => $countMail]);
    }

    public function compose(Request $request)
    {
        $abandoned = [];

        foreach ($request->get('items') as $item) {
            $pid = explode(':', $item);
            $cart = collect($this->api2cart->getAbandonedCart($pid[0]));
            $storeInfo = $this->api2cart->getCart($pid[0]);

            // adding selected abandoned carts
            foreach ($cart as $ci) {
                if (isset($ci['customer']['id']) && $ci['id'] == $pid[1]) {
                    $ci['store_info'] = $storeInfo;
                    $ci['store_key'] = $pid[0];
                    $abandoned[] = $ci;
                }
            }
        }


        foreach ($abandoned as $key => $op) {
            foreach ($op['order_products'] as $item) {
                if ($productInfo = $this->api2cart->getProductInfo($op['store_key'], $item['product_id'])) {
                    $abandoned[$key]['products'][] = $productInfo;
                }
            }
        }

        return view('emails.abandoned', compact('abandoned'));
    }

}
