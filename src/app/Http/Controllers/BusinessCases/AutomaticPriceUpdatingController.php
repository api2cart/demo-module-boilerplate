<?php

namespace App\Http\Controllers\BusinessCases;

use App\Http\Controllers\Controller;
use App\Http\Requests\BusinessCases\AutomaticPriceUpdateRequest;
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

    public function store(AutomaticPriceUpdateRequest $request)
    {
        Log::debug( $request->all() );

        $products = [];
        $carts = collect($this->api2cart->getCartList());

        $formData = $request->except('_hash');
        $formData['model'] = $formData['sku'];

        foreach ($carts as $item){

            $result = $this->api2cart->addProduct($item['store_key'], $formData);

            // add image for each product
            if ( isset($result['result']['product_id']) && $result['result']['product_id'] != '' ){

                $this->api2cart->addProductImage( $item['store_key'], $result['result']['product_id'],
                    // image data goes here
                    [
                        'type'          => 'base',
                        'image_name'    => '0011ff.png',
                        'url'           => 'https://dummyimage.com/600x400/ffffff/0011ff.png'
                    ]
                );

            }

            if ($result['result']['product_id'] != ''){
                $products[] = [
                    'store_key'     => $item['store_key'],
                    'product_id'    => $result['result']['product_id'],
                    'sku'           => $request->get('sku'),
                ];
            }

        }


//        Log::debug( print_r($products,1) );

        session()->put('automatic_price_updating', $products);

        return response()->json(['items' => $products,'log' => $this->api2cart->getLog() ]);


    }


    public function products(Request $request)
    {
        $products = [];
        $products_ids = session()->get('automatic_price_updating');

//        Log::debug( print_r($products_ids,1) );

        if ( !$products_ids ) return response(null, 404);

        foreach ($products_ids as $item){
            $tmp = $this->api2cart->getProductInfo( $item['store_key'], $item['product_id'] );
            if ($tmp) {
                $tmp['store_key'] = $item['store_key'];
                $products[] = $tmp;
            }
        }

//        Log::debug( print_r($products,1) );


        return response()->json(['items'=>$products]);

    }

    public function edit(Request $request)
    {
        $this->api2cart->debug = false;

        $products = [];
        $products_ids = session()->get('automatic_price_updating');

        foreach ($products_ids as $item){
            $tmp = $this->api2cart->getProductInfo( $item['store_key'], $item['product_id'] );
            if ($tmp) {
                $tmp['store_key'] = $item['store_key'];
                $products[] = $tmp;
            }
        }

        if ( count($products) != count($products_ids) ){
            // looks stores reseting
//            Log::debug( print_r($products,1) );
            session()->flash('automatic_price_updating');
            return response('Please create new product scope',404);
        }

        $product = $products[0];

        if ( $request->ajax() ){
            return response()->json(['data' => view('business_cases.automatic_price_updating.form', compact('products','product'))->render(), 'log' => $this->api2cart->getLog() ]);
        }
    }


    public function update(AutomaticPriceUpdateRequest $request)
    {
        $products_ids = session()->get('automatic_price_updating');
        $formData = $request->except('_token','_method');
        $formData['model'] = $formData['sku'];

        foreach ($products_ids as $item){
            $this->api2cart->updateProduct( $item['store_key'], $item['product_id'], $formData );
        }

        return response()->json(['log' => $this->api2cart->getLog()]);

    }

}
