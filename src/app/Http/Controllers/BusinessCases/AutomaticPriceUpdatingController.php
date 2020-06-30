<?php

namespace App\Http\Controllers\BusinessCases;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;


use App\Services\Api2Cart;


class AutomaticPriceUpdatingController extends Controller
{

    private $api2cart;

    public function __construct(Api2Cart $api2Cart)
    {
        $this->api2cart = $api2Cart;
    }

    public function index()
    {

//        $result = $this->api2cart->getProductList( "1316ad9a66ac871ce46a3d59005acc9c", null, null, null, null , null );
//        session()->put('automatic_price_updating', collect( $result['result']['product'] ) );
//
//        $products = session()->get('automatic_price_updating');
//
//        print_r($products);

        return view('business_cases.automatic_price_updating.index');
    }


    public function create(Request $request)
    {
        if ( $request->ajax() ){
            return response()->json(['data' => view('business_cases.automatic_price_updating.form')->render(), 'log' => $this->api2cart->getLog() ]);
        }

        return redirect(route('business_cases.automatic_price_updating'));
    }

    public function store(Request $request)
    {
//        Log::debug( $request->all() );
        $result = [];
        $carts = collect($this->api2cart->getCartList());

        $formData = $request->except('_hash');
        $formData['model'] = $formData['sku'];
//        $formData['image_url'] = "https://dummyimage.com/600x400/ffffff/0011ff.png&text=Product+Image";
//        $formData['image_name'] = "0011ff.png&text=Product+Image";

        foreach ($carts as $item){

            $result = $this->api2cart->addProduct($item['store_key'], $formData);
            Log::debug( print_r($item,1) );
            Log::debug( print_r($result,1) );

            //TODO: add image for each product
            if ( isset($result['result']['product_id']) && $result['result']['product_id'] ){


                $this->api2cart->addProductImage( $item['store_key'], $result['result']['product_id'],
                    // image data goes here
                    [
                        'type'          => 'base',
                        'image_name'    => '0011ff.png&text=Product+Image',
                        'url'           => 'https://dummyimage.com/600x400/ffffff/0011ff.png&text=Product+Image'
                    ]
                );

            }


            $result[] = [
                'store_key'     => $item['store_key'],
                'product_id'    => $result['result']['product_id'],
                'sku'           => $request->get('sku')
            ];

        }









    }

}
