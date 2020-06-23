<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Services\Api2Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CategoriesController extends Controller
{
    private $api2cart;


    public function __construct(Api2Cart $api2Cart)
    {
        $this->api2cart = $api2Cart;
    }

    public function index()
    {
        return view('categories.index');
    }

    public function categoryList($store_id = null, Request $request)
    {
        \Debugbar::disable();

        /**
         * get account carts & extract exact store info
         */
        $carts = collect($this->api2cart->getCartList());
        $storeInfo = $carts->where('store_key', $store_id)->first();

        $totalItems = $this->api2cart->getCategoryCount( $store_id );

//        Log::debug( $store_id );


        $items = collect([]);

        if ( $totalItems ){

            $result = $this->api2cart->getCategoryList( $store_id );

            $newRes= (isset($result['result']['categories_count'])) ? collect( $result['result']['category'] ) : collect([]);
            // put additional information
            if ( $newRes->count() ){
                foreach ($newRes as $item){
                    $newItem = $item;
                    $newItem['cart_id'] = $storeInfo['cart_id'];
                    if ( !isset($newItem['parent_id']) ) $newItem['parent_id'] = 0;
                    $items->push( $newItem );
                }
            }

            if ( isset($result['pagination']['next']) && strlen($result['pagination']['next']) ){
                // get next iteration to load rest customers
                while( isset($result['pagination']['next']) && strlen($result['pagination']['next']) ){
                    $result = $this->api2cart->getCategoryListPage( $store_id , $result['pagination']['next']);
                    $newRes = (isset($result['result']['categories_count'])) ? collect( $result['result']['category'] ) : collect([]);
                    // put additional information
                    if ( $newRes->count() ){
                        foreach ($newRes as $item){
                            $newItem = $item;
                            $newItem['cart_id'] = $storeInfo['cart_id'];
                            if ( !isset($newItem['parent_id']) ) $newItem['parent_id'] = 0;
                            $items->push( $newItem );
                        }
                    }
                }

            }


        }


        $tree = $this->buildTree( $items->sortBy('id')->toArray() );

        $items = $items->map(function ($item, $key) use ($tree) {
            $newItem = $item;
            $result = array();
            if ( isset($item['parent_id']) && intval($item['parent_id']) ){
                $this->buildBreadcrumb( $tree, $item['id'], $result);
            }
            $newItem['parent_name'] = implode(" >> ",array_reverse($result));
            return $newItem;
        });



        $data = [
            "recordsTotal"      => $totalItems,
            "recordsFiltered"   => $totalItems,
            "start"             => 0,
            "length"            => 10,
            "data"              => $items->toArray(),

            'log'               => $this->api2cart->getLog(),
        ];

        return response()->json($data);


    }

    public function edit($store_id=null, $category_id=null, Request $request)
    {
        \Debugbar::disable();

        $category = $this->api2cart->getCategoryInfo( $store_id, $category_id );

        if ( $request->ajax() ){
            return response()->json(['data' => view('categories.form',compact('category','store_id', 'category_id'))->render(), 'item' => $category,'log' => $this->api2cart->getLog() ]);
        }

        return redirect(route('categories.index'));

    }


    public function update($store_id=null, $category_id=null, CategoryRequest $request)
    {
        \Debugbar::disable();

        $result = $this->api2cart->updateCategory( $store_id, $category_id, $request->all() );

//        Log::debug("{$store_id} {$category_id}");
//        Log::debug( $request->all() );

        if ( $request->ajax() ){
            return response()->json(['item' => $result, 'log' => $this->api2cart->getLog()]);
        }

    }






    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy($store_id=null, $category_id=null)
    {
        if ( $this->api2cart->deleteCategory($store_id , $category_id ) ){
            return response()->json([ 'log' => $this->api2cart->getLog() ]);
        } else {
            return response()->json([ 'log' => $this->api2cart->getLog() ], 404);
        }

    }






    private function buildTree(array $elements, $parentId = 0)
    {

        $branch = array();

        foreach ($elements as $k=>$element) {
            if (isset($element['parent_id']) && $element['parent_id'] == $parentId) {
                $children = $this->buildTree($elements, $element['id']);
                if ($children) {
                    $element['children'] = $children;
                }
                $branch[ $element['id'] ] = $element;
            }
        }

        return $branch;


    }

    private function buildBreadcrumb($tree, $needle, &$result = array())
    {
        $result = array();

        if (is_array($tree)) {
            foreach ($tree as $node) {
                if ($node['id'] == $needle) {
                    // uncomment if you need include self in breadcrumbs
//                    $result[] = $node['name'];
                    return true;
                } else if (!empty($node['children'])) {
                    if ($this->buildBreadcrumb($node['children'], $needle, $result)){
                        $result[] = $node['name'];
                        return true;
                    }
                }
            }
        } else {
            if ($tree == $needle) {
                $result[] = $tree;
                return true;
            }
        }
        return false;
    }



}
