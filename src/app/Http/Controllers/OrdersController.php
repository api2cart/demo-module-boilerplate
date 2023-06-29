<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\Http\Requests\OrderShipmentRequest;
use App\Services\Api2Cart;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Api2Cart\Client\Model\OrderShipmentAdd as OrderShipmentContainer;
use \Api2Cart\Client\Model\OrderShipmentAddTrackingNumbers as TrackingNumbers;
use \Api2Cart\Client\Model\OrderShipmentAddItems as ShipmentItems;

class OrdersController extends Controller
{
    const SHIPMENT_STATUS_NOT_SHIPPED       = 0;
    const SHIPMENT_STATUS_PARTIALLY_SHIPPED = 1;
    const SHIPMENT_STATUS_SHIPPED           = 2;

    private $api2cart;


    public function __construct(Api2Cart $api2Cart)
    {
        $this->api2cart = $api2Cart;
    }

    public function index()
    {
        return view('orders.index');
    }

    public function orderList($store_id=null,Request $request)
    {
        \Debugbar::disable();

        /**
         * get account carts & extract exact store info
         */
        $carts = collect($this->api2cart->getCartList());

        $totalOrders = 0;
        $orders = collect([]);
        $logs = collect([]);

        $sort_by      = ($request->get('sort_by')) ? $request->get('sort_by') : null;
        $sort_direct  = ($request->get('sort_direct')) ? true : false;
        $created_from = ($request->get('created_from')) ? Carbon::parse($request->get('created_from'))->format("Y-m-d\TH:i:sO") : null;
        $limit        = ($request->get('limit')) ? (string)$request->get('limit') : null;
        $length       = ($request->get('length')) ? $request->get('length') : 15;
        $storeKeys    = ($request->get('storeKeys')) ?: ($store_id !== null ? [$store_id] : []);


        foreach ($storeKeys as $store_id) {
            $storeInfo = $carts->where('store_key', $store_id)->first();
            $totalOrders = $this->api2cart->getOrderCount( $store_id );

            if ( $totalOrders ) {

                $result = $this->api2cart->getOrderList(
                    $store_id,
                    $sort_by,
                    null,
                    $limit,
                    $created_from
            );

                $newOrders = (isset($result['result']['orders_count'])) ? collect( $result['result']['order'] ) : collect([]);

                // put additional information
                if ( $newOrders->count() ){
                    foreach ($newOrders as $item){
                        $newItem = $item;
                        $newItem['create_at']['value'] = Carbon::parse($item['create_at']['value'])->setTimezone('UTC')->format("Y-m-d\TH:i:sO");
                        $newItem['cart_id'] = $storeInfo['cart_id'];
                        $orders->push( $newItem );
                    }
                }


                if ( isset($result['pagination']['next']) && strlen($result['pagination']['next']) ){
                    // get next iteration to load rest orders
                    while( isset($result['pagination']['next']) && strlen($result['pagination']['next']) ){
                        $result = $this->api2cart->getOrderListPage( $store_id , $result['pagination']['next']);
                        $newOrders = (isset($result['result']['orders_count'])) ? collect( $result['result']['order'] ) : collect([]);

                        // put additional information
                        if ( $newOrders->count() ){
                            foreach ($newOrders as $item){
                                $newItem = $item;
                                $newItem['create_at']['value'] = Carbon::parse($item['create_at']['value'])->setTimezone('UTC')->format("Y-m-d\TH:i:sO");
                                $newItem['cart_id'] = $storeInfo['cart_id'];
                                $orders->push( $newItem );
                            }
                        }
                    }

                }

                foreach ($this->api2cart->getLog()->all() as $item) {
                    $logs->push($item);
                }
            }
        }

        if ( $sort_by  ){
            switch ($sort_by){
                case 'create_at':
                    $sort_by = 'create_at.value';
                    break;
                default:
                    $sort_by = 'create_at.value';
                    break;
            }
            $sorted = $orders->sortBy($sort_by, null, $sort_direct );
        } else {
            $sorted = $orders->sortBy('create_at.value', null, $sort_direct );
        }

        $data = [
            "recordsTotal"      => $totalOrders,
            "recordsFiltered"   => $totalOrders,
            "start"             => 0,
            "length"            => $length,
            "data"              => ($length) ? $sorted->forPage(0, $length) : $sorted->toArray(),
            'log'               => $logs,
        ];

        return response()->json($data);
    }


    public function orderInfo($store_id=null,$order_id=null,Request $request)
    {
        $order = $this->api2cart->getOrderInfo( $store_id, $order_id );

        if ( $request->ajax() ){
            return response()->json(['data' => view('orders.info',compact('order','store_id', 'order_id'))->render(), 'item' => $order,'log' => $this->api2cart->getLog() ]);
        }

        return redirect( route('orders.index') );
    }

    public function orderProducts($store_id=null,$order_id=null,Request $request)
    {
        $order = $this->api2cart->getOrderInfo( $store_id, $order_id );

        /**
         * get all product's id to featch from 1 request insteed 1 per product
         *

        $pids = [];
        foreach ( $order['order_products'] as $item ){
            $pids[] = $item['product_id'];
        }
        $products = [];
        if (count($pids)){
            $products = $this->api2cart->getProductList( $store_id, $pids );
        }

        */


        if ( $request->ajax() ){
            return response()->json(['data' => view('orders.products',compact('order','store_id', 'order_id'))->render(), 'item' => $order,'log' => $this->api2cart->getLog() ]);
        }

        return redirect( route('orders.index') );
    }

    public function abandoned($store_id=null, Request $request)
    {
        $data = [
            "recordsTotal" => 0,
            "recordsFiltered" => 0,
            "start" => 0,
            "length" => 10,
            "data" => collect([]),
            'log' => $this->api2cart->getLog(),
        ];

        if ($store_id) {
            $items = collect($this->api2cart->getAbandonedCart($store_id));
            $data = [
                "recordsTotal" => (is_array($items)) ? count($items) : 0,
                "recordsFiltered" => (is_array($items)) ? count($items) : 0,
                "start" => 0,
                "length" => 10,
                "data" => collect($items),
                'log' => $this->api2cart->getLog(),
            ];
        } elseif ($storeIds = $request->get('storeKeys', [])) {
            $orders = collect([]);

            foreach ($storeIds as $store_id) {
                $items = collect($this->api2cart->getAbandonedCart($store_id));

                if ($items->count()) {
                    foreach ($items as $item) {
                        $newItem = $item;
                        $newItem['cart_id'] = $store_id;
                        $newItem['created_at']['value'] = Carbon::parse($item['created_at']['value'])->setTimezone('UTC')->format("Y-m-d\TH:i:sO");
                        $orders->push($newItem);
                    }
                }
            }

            $data = [
                "recordsTotal" => $orders->count(),
                "recordsFiltered" => $orders->count(),
                "start" => 0,
                "length" => 10,
                "data" => $orders->sortBy('created_at.value', null, true)->toArray(),
                'log' => $this->api2cart->getLog(),
            ];
        }

        return response()->json($data);
    }

    public function statuses($store_id=null, Request $request)
    {
        $statuses = $this->api2cart->getOrderStatuses( $store_id );

        if ( !$statuses ){
            return response()->json(['log' => $this->api2cart->getLog() ], 404);
        }

        if ( $request->ajax() ){
            return response()->json(['data' => collect($statuses['cart_order_statuses']), 'log' => $this->api2cart->getLog() ]);
        }

        return redirect( route('orders.index') );
    }

    public function create(Request $request)
    {
        $carts = collect($this->api2cart->getCartList());

        if ( $request->ajax() ){
            return response()->json( ['data' => view('orders.form', compact('carts'))->render(), 'item' => $carts ] );
        }

        return redirect( route('orders.index') );
    }

    public function store(OrderRequest $request)
    {
        $cart = $this->api2cart->getCart( $request->get('cart_id') );
        $customer = $this->api2cart->getCustomer( $request->get('cart_id'), $request->get('customer_id') );

        $address = collect( $customer['address_book'] );
        $billing = $address->where('type', 'billing')->first();
        $shipping= $address->where('type', 'shipping')->first();

        // for any case if only shipping avialable
        if ($billing == null) $billing = $shipping;

        // some customers do not have state
        if ( !isset($shipping['state']['code']) || $shipping['state']['code'] == '' ) $shipping['state']['code'] = 'AL';

        $order = [
            'store_id'          => $cart['stores_info'][0]['store_id'],
            'customer_email'    => $customer['email'],
            'order_status'      => $request->get('status_id'),
            'subtotal_price'    => 0,
            'total_price'       => 0,

            'bill_first_name'   => $billing['first_name'] ?: $billing['first_name'] ?: $shipping['first_name'] ?: 'John',
            'bill_last_name'    => $billing['last_name'] ?: $billing['last_name'] ?: $shipping['last_name'] ?: 'Doe',
            'bill_address_1'    => $billing['address1'] ?: $billing['address1'] ?: $shipping['address1'] ?: '221b, Baker street',
            'bill_city'         => $billing['city'] ?: $billing['city'] ?: $shipping['city'] ?: 'London',
            'bill_postcode'     => $billing['postcode'] ?: $billing['postcode'] ?: $shipping['postcode'] ?: '00000',

            // state & country need be cleared
            'bill_state'        => (isset($billing['state']['code']) && $billing['state']['code'] != '') ? $billing['state']['code'] : $shipping['state']['code'],
            'bill_country'      => $billing['country']['code3'] ?: $billing['country']['code3'] ?: $shipping['country']['code3'] ?: 'UK',
        ];

        foreach ($request->get('checked_id') as $cpi){
            $product  = $this->api2cart->getProductInfo( $request->get('cart_id'), $cpi );
            $quantity = $request->get('product_quantity')[ array_search($cpi, $request->get('product_id')) ];

            // check if quantity right
            if ($product['quantity'] < $quantity && $product['is_stock_managed'] === true) {
                continue;
            }

            $order['order_item'][] = [
                'order_item_id'         => $product['id'],
                'order_item_name'       => $product['name'],
                'order_item_model'      => $product['u_model'],
                'order_item_price'      => $product['price'],
                'order_item_quantity'   => $quantity
            ];

            $order['subtotal_price']    += $product['price'] * $quantity;
            $order['total_price']       += $product['price'] * $quantity;
        }

        list($returnCode, $result) = $this->api2cart->createOrder( $request->get('cart_id') , $order );

        if ($returnCode == 0){
            return response()->json([ 'log' => $this->api2cart->getLog(), 'success' => true, 'item' => $this->api2cart->getOrderInfo( $request->get('cart_id'), $result['order_id'] ) ]);
        } else {
            // error creating order
            return response()->json([ 'log' => $this->api2cart->getLog(), 'success' => false, 'errormessage' => $result ]);
        }

    }

    /**
     * @param null    $storeKeys Store keys array
     * @param Request $request   Request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getOrdersWithShipments($storeKeys = null, Request $request)
    {
        $limit = ($request->get('limit')) ? (string)$request->get('limit') : 15;

        if ($storeKeys === null) {
            $stores = \Cache::remember('all_stores', 3600, function () {
                return collect($this->api2cart->getCartList());
            });

            if ($stores->count() === 0) {//fix
                \Cache::delete('all_stores');
                $stores = \Cache::remember('all_stores', 3600, function () {
                    return collect($this->api2cart->getCartList());
                });
            }
        } else {
            $cartId = ($request->get('cart_id')) ? (string)$request->get('cart_id') : null;
            $stores = [['cart_id' => $cartId, 'store_key' => $storeKeys]];
        }

        $user = Auth::user();

        if (App::environment() === 'development') {
            $env = ['USER_ID' => $user->id, 'PHP_IDE_CONFIG' => 'serverName=API2Cart'];
        } else {
            $env = ['USER_ID' => $user->id];
        }

        $processes = [];

        foreach ($stores as $store) {
            $processes[] =
                [
                    'php',
                    base_path('artisan'),
                    'sendRequestToA2C',
                    'getOrderList',
                    base64_encode(
                        json_encode(['cart_id' => $store['cart_id'], $store['store_key'], null, null, $limit, null])
                    ),
                    'order',
                    'orders_count',
                    '--subEntities=getOrderShipmentsAsync',
                    '--subEntityId=order_id'
                ];
        }

        $runningProcesses = $allProcesses = [];

        foreach ($processes as $command) {

            while (count($runningProcesses) >= 5) {
                foreach ($runningProcesses as $index => $process) {
                    if (!$process->isRunning()) {

                        unset($runningProcesses[$index]);
                        break;
                    }
                }

                usleep(1000000);
            }

            $process = new Process($command);
            $process->setEnv($env);
            $process->start();

            $runningProcesses[] = $process;
            $allProcesses[] = $process;
        }

        foreach ($runningProcesses as $process) {
            $process->wait();
        }

        $logs = collect([]);
        $orders = collect([]);

        foreach ($allProcesses as $output) {
            if ($output->isSuccessful()) {
                $data = json_decode($output->getOutput());

                foreach (collect($data->result) as $order) {
                    $itemsToShip = $order->order_products;

                    foreach ($itemsToShip as $item) {
                        $item->order_quantity = $item->quantity;
                        $item->shipped_quantity = 0;

                        if (!empty($order->sub_entities->result->shipment)) {
                            foreach ($order->sub_entities->result->shipment as $shipment) {
                                if ($shipment->items) {
                                    foreach ($shipment->items as $shipmentItem) {
                                        if (isset($shipmentItem->order_product_id)
                                            && $shipmentItem->order_product_id === $item->order_product_id
                                        ) {
                                            $item->quantity -= $shipmentItem->quantity;
                                            $item->shipped_quantity += $shipmentItem->quantity;
                                        }
                                    }
                                }
                            }
                        }
                    }

                    $shipmentStatus = self::SHIPMENT_STATUS_NOT_SHIPPED;

                    foreach ($itemsToShip as $item) {
                        if ($item->quantity == 0) {
                            $shipmentStatus = self::SHIPMENT_STATUS_SHIPPED;
                        } elseif ($item->quantity < $item->order_quantity) {
                            $shipmentStatus = self::SHIPMENT_STATUS_PARTIALLY_SHIPPED;
                            break;
                        }
                    }

                    if (!empty($order->sub_entities->result->shipment)
                        && $shipmentStatus === self::SHIPMENT_STATUS_NOT_SHIPPED
                    ) {
                        $shipmentStatus = self::SHIPMENT_STATUS_SHIPPED;
                    }

                    $order->avail_shipment_items = $itemsToShip;
                    $order->shipment_status = $shipmentStatus;
                    $orders->push($order);
                }

                foreach (collect($data->logs) as $log) {
                    $logs->push($log);
                }
                $res[] = ['result' => collect($data->result), 'logs' => collect($data->logs)];
            } else {
                $res[] = $output->getExitCodeText();
            }
        }

        $result = [
            "stores" => $stores,
            "recordsTotal" => $orders->count(),
            "recordsFiltered" => $orders->count(),
            "start" => 0,
            "length" => 10,
            "data" => $orders->sortBy('created_at.value', null, true)->toArray(),
            'log' => $logs->all(),
        ];

        if ($result['recordsTotal']) {
            Cache::put('OrdersWithShipments' . md5(json_encode($storeKeys)), $result, 3600);
        }

        return response()->json($result);
    }

    /**
     * @param string|null    $storeKey Store Key
     * @param string|null    $orderId  OrderId
     * @param Request $request  Request
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Throwable
     */
    public function orderShipments($storeKey = null, $orderId = null, Request $request)
    {
        $cartInfo = \Cache::remember('cartInfo' . $storeKey, 3600, function () use ($storeKey) {
            return $this->api2cart->getCart($storeKey);
        });

        $carriers = $cartInfo['stores_info'][0]['carrier_info'] ?? [];

        if ($carriers) {
            $carriers = array_combine(array_column($carriers, 'id'), $carriers);
        }

        $shipments = $this->_getAllOrderShipments($storeKey, $orderId);

        $logs = collect([]);

        foreach ($this->api2cart->getLog()->all() as $item) {
            $logs->push($item);
        }

        if ($request->ajax()) {
            return response()->json(['data' => view('orders.shipments.info', compact('orderId', 'shipments', 'carriers'))->render(), 'shipments' => $shipments, 'log' => $logs]);
        }

        return redirect(route('orders.get-orders-with-shipments'));
    }

    /**
     * @param null    $storeKey Store Key
     * @param null    $orderId  Order ID
     * @param Request $request  Request
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Throwable
     */
    public function orderShipmentAdd($storeKey = null, $orderId = null, Request $request)
    {
        $cartInfo = \Cache::remember('cartInfo' . $storeKey, 3600, function () use ($storeKey) {
            return $this->api2cart->getCart($storeKey);
        });

        $carriers = $cartInfo['stores_info'][0]['carrier_info'] ?? [];

        if ($carriers) {
            $carriers = array_combine(array_column($carriers, 'id'), $carriers);
        }

        list($itemsToShip, $shipmentStatus) = $this->_prepareItemsToShip($storeKey, $orderId);

        if ($request->ajax()) {
            return response()->json(
                [
                    'data' => view('orders.shipments.form', compact('itemsToShip', 'carriers', 'shipmentStatus', 'orderId', 'storeKey'))->render(),
                    'shipmentStatus' => $shipmentStatus
                ]
            );
        }

        return redirect(route('orders.index'));
    }

    /**
     * @param OrderShipmentRequest $request Request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function orderShipmentStore(OrderShipmentRequest $request)
    {
        $shipment = new OrderShipmentContainer();
        $orderId = $request->get('order_id');
        $storeKey = $request->get('store_key');

        $shipment->setOrderId($orderId);

        $trackingNumbers = new TrackingNumbers();
        $trackingNumbers->setCarrierId($request->get('carrier_id'));
        $trackingNumbers->setTrackingNumber((string)$request->get('tracking_number'));
        $shipment->setTrackingNumbers([$trackingNumbers]);

        if ($request->get('all_products') !== 'on' && $items = $request->get('items')) {
            $shipmentItems = [];

            foreach ($items as $orderProductId => $value) {
                if ($value['quantity'] > 0) {
                    $shipmentItem = new ShipmentItems();
                    $shipmentItem->setOrderProductId($orderProductId);
                    $shipmentItem->setQuantity($value['quantity']);
                    $shipmentItems[] = $shipmentItem;
                }
            }

            $shipment->setItems($shipmentItems);
        }

        list($returnCode, $result) = $this->api2cart->createOrderShipment($storeKey, $shipment);

        if ($returnCode == 0) {
            list($itemsToShip, $shipmentStatus) = $this->_prepareItemsToShip($storeKey, $orderId);

            return response()->json(['log' => $this->api2cart->getLog(), 'success' => true, 'shipment_status' => $shipmentStatus]);
        } else {
            return response()->json(['log' => $this->api2cart->getLog(), 'success' => false, 'errormessage' => $result]);
        }
    }

    /**
     * @param string $storeKey Store Key
     * @param string $orderId  Order ID
     *
     * @return array
     */
    protected function _getAllOrderShipments($storeKey, $orderId): array
    {
        $shipments = collect([]);
        $result = $this->api2cart->getOrderShipments($storeKey, $orderId);
        $resEntities = (!empty($result['result']['shipment_count']))
            ? collect($result['result']['shipment'])
            : collect([]);

        if ($resEntities->count()) {
            foreach ($resEntities as $item) {
                $shipments->push($item);
            }
        }

        if (isset($result['pagination']['next']) && strlen($result['pagination']['next'])) {
            while (isset($result['pagination']['next']) && strlen($result['pagination']['next'])) {
                $result = $this->api2cart->getOrderShipments($storeKey, $orderId, $result['pagination']['next']);
                $resEntities = (!empty($result['result']['shipment_count']))
                    ? collect($result['result']['shipment'])
                    : collect([]);

                if ($resEntities->count()) {
                    foreach ($resEntities as $item) {
                        $shipments->push($item);
                    }
                }
            }
        }

        return $shipments->all();
    }

    /**
     * @param string $storeKey Store Key
     * @param string $orderId  Order ID
     *
     * @return array
     */
    protected function _prepareItemsToShip($storeKey, $orderId): array
    {
        $order = $this->api2cart->getOrderInfo($storeKey, $orderId);
        $shipments = $this->_getAllOrderShipments($storeKey, $orderId);
        $itemsToShip = $order['order_products'];

        foreach ($itemsToShip as $key => $item) {
            $itemsToShip[$key]['order_quantity'] = $item['quantity'];
            $itemsToShip[$key]['shipped_quantity'] = 0;

            foreach ($shipments as $shipment) {
                if ($shipment['items']) {
                    foreach ($shipment['items'] as $shipmentItem) {
                        if (isset($shipmentItem['order_product_id'])
                            && $shipmentItem['order_product_id'] === $item['order_product_id']
                        ) {
                            $itemsToShip[$key]['quantity'] -= $shipmentItem['quantity'];
                            $itemsToShip[$key]['shipped_quantity'] += $shipmentItem['quantity'];
                        }
                    }
                }
            }
        }

        $shipmentStatus = self::SHIPMENT_STATUS_NOT_SHIPPED;

        foreach ($itemsToShip as $item) {
            if ($item['quantity'] == 0) {
                $shipmentStatus = self::SHIPMENT_STATUS_SHIPPED;
            } elseif ($item['quantity'] < $item['order_quantity']) {
                $shipmentStatus = self::SHIPMENT_STATUS_PARTIALLY_SHIPPED;
                break;
            }
        }

        if ($shipments && $shipmentStatus === self::SHIPMENT_STATUS_NOT_SHIPPED) {
            $shipmentStatus = self::SHIPMENT_STATUS_SHIPPED;
        }

        return [$itemsToShip, $shipmentStatus];
    }

}
