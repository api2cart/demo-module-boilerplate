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
    Route::get('/stores', 'StoresController@index')->name('stores.index');
    Route::any('/stores/list', 'StoresController@storeList')->name('stores.list');
    Route::get('/stores/details/{id?}', 'StoresController@storeDetails')->name('stores.details');

    Route::get('/orders', 'OrdersController@index')->name('orders.index');
    Route::post('/orders/list/{store_id?}', 'OrdersController@orderList')->name('orders.list');

    Route::get('/products', 'ProductsController@index')->name('products.index');
    Route::post('/products/list/{store_id?}', 'ProductsController@productList')->name('products.list');

    Route::get('/customers', 'CustomersController@index')->name('customers.index');
    Route::post('/customers/list/{store_id?}', 'CustomersController@customerList')->name('customers.list');

});


