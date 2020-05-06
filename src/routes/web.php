<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
//    return view('welcome');
    return redirect( '/home' );
});

//Auth::routes();
Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('login', 'Auth\LoginController@login');
Route::post('logout', 'Auth\LoginController@logout')->name('logout');


Route::middleware(['auth'])->group(function () {
    Route::resource('users', 'UsersController');
});

Route::middleware(['auth', 'apikey'])->group(function () {

    Route::get('/home', 'HomeController@index')->name('home');

    Route::resource('stores', 'StoresController');
    Route::any('/stores/list', 'StoresController@storeList')->name('stores.list');
    Route::get('/stores/details/{id?}', 'StoresController@storeDetails')->name('stores.details');

    Route::get('/orders', 'OrdersController@index')->name('orders.index');
    Route::post('/orders/list/{store_id?}', 'OrdersController@orderList')->name('orders.list');
    Route::get('/orders/{store_id?}/{order_id?}', 'OrdersController@orderInfo')->name('orders.info');
    Route::get('/orders/{store_id?}/{order_id?}/products', 'OrdersController@orderProducts')->name('orders.products');

    Route::get('/products', 'ProductsController@index')->name('products.index');
    Route::post('/products/list/{store_id?}', 'ProductsController@productList')->name('products.list');
    Route::delete('/products/{store_id?}/{product_id?}', 'ProductsController@destroy')->name('products.delete');
    Route::get('/products/{store_id?}/{product_id?}/edit', 'ProductsController@edit')->name('products.edit');
    Route::post('/products/{store_id?}/{product_id?}', 'ProductsController@update')->name('products.update');
    Route::post('/products/{store_id?}/{product_id?}/delete_image', 'ProductsController@destroyImage')->name('products.deleteImage');

    Route::get('/customers', 'CustomersController@index')->name('customers.index');
    Route::post('/customers/list/{store_id?}', 'CustomersController@customerList')->name('customers.list');

    Route::get('/categories', 'CategoriesController@index')->name('categories.index');
    Route::post('/categories/list/{store_id?}', 'CategoriesController@categoryList')->name('categories.list');
    Route::get('/categories/{store_id?}/{category_id?}/edit', 'CategoriesController@edit')->name('categories.edit');
    Route::post('/categories/{store_id?}/{category_id?}', 'CategoriesController@update')->name('categories.update');
    Route::delete('/categories/{store_id?}/{category_id?}', 'CategoriesController@destroy')->name('categories.delete');

});


