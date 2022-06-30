<?php

namespace App\Http\Controllers\BusinessCases;

use App\Http\Controllers\Controller;
use App\Http\Requests\BusinessCases\AutomaticPriceUpdateRequest;
use Carbon\Carbon;
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
        return view('business_cases.automatic_price_updating.index');
    }


    public function create(Request $request)
    {
        if ( $request->ajax() ){
            $carts = collect($this->api2cart->getCartList());
            $isCreate = true;
            return response()->json(['data' => view('business_cases.automatic_price_updating.form', compact('isCreate', 'carts'))->render(), 'log' => $this->api2cart->getLog() ]);
        }

        return redirect(route('business_cases.automatic_price_updating'));
    }

    public function store(AutomaticPriceUpdateRequest $request)
    {
        $formData = $request->except('_hash');
        $formData['model'] = $formData['sku'];

        list($returnCode, $result) = $this->api2cart->addProduct($formData['cart_id'], $formData);

        if ($returnCode == 0) {
            // add image to product
            if ( isset($result['result']['product_id']) && $result['result']['product_id'] != '' ) {

                $this->api2cart->addProductImage($formData['cart_id'], $result['result']['product_id'],
                    // image data goes here
                    [
                        'type'          => 'base',
                        'image_name'    => '0011ff.png',
                        'url'           => 'https://dummyimage.com/600x400/ffffff/0011ff.png'
                    ]
                );
            }

            return response()->json([ 'log' => $this->api2cart->getLog(), 'success' => true, 'item' => $this->api2cart->getProductInfo( $formData['cart_id'], $result['ptoduct_id'] ) ]);
        } else {
            return response()->json([ 'log' => $this->api2cart->getLog(), 'success' => false, 'errormessage' => $result ]);
        }
    }


    public function products(Request $request)
    {
        $products = collect([]);
        $limit = 5;
        $carts = $request->get('store_keys', []);

        foreach ($carts as $storeKey) {
            $productsInfo = $this->api2cart->getProductList($storeKey, null, 'create_at', 'desc', $limit);

            if ($productsInfo) {
                foreach ($productsInfo['result']['product'] ?? [] as $productInfo) {
                    $productInfo['store_key'] = $storeKey;
                    $productInfo['create_at']['value'] = Carbon::parse($productInfo['create_at']['value'])->setTimezone('UTC')->format("Y-m-d\TH:i:sO");
                    $products->push($productInfo);
                }
            }
        }

        $items = [];

        foreach ($products->sortBy('create_at.value', null, true)->toArray() as $item) {
            $items[] = $item;
        }

        return response()->json(['items' => $items]);
    }

    public function edit(Request $request)
    {
        $this->api2cart->debug = false;
        $isCreate = false;
        $storeKey = $request->get('store_key', '');
        $productId = $request->get('id', '');

        $product = [];

        if ($storeKey && $productId) {
            $res = $this->api2cart->getProductInfo($storeKey, $productId);

            if ($res) {
                $res['store_key'] = $storeKey;
                $product = $res;
            }
        }

        if ( $request->ajax() ){
            return response()->json([
                'data' => view('business_cases.automatic_price_updating.form', compact('productId', 'storeKey', 'product', 'isCreate'))->render(),
                'log' => $this->api2cart->getLog()
            ]);
        }
    }


    public function update(AutomaticPriceUpdateRequest $request)
    {
        $formData = $request->except('_token','_method');

        $this->api2cart->updateProduct($formData['cart_id'], $formData['product_id'], $formData);

        return response()->json(['log' => $this->api2cart->getLog()]);

    }

}
