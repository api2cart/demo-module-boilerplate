<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use Illuminate\Support\Facades\Log;

use App\Models\User;
use App\Services\Api2Cart;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;


class UsersController extends Controller
{

    private $api2cart;


    public function __construct(Api2Cart $api2Cart)
    {
        $this->api2cart = $api2Cart;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {

        return redirect( '/home' );

//        $keyword = $request->get('search');
//        $perPage = 25;
//
//        if (!empty($keyword)) {
//            $users = User::latest()->paginate($perPage);
//        } else {
//            $users = User::latest()->paginate($perPage);
//        }
//
//        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(UserRequest $request)
    {

        $requestData = $request->all();

        User::create($requestData);

        return redirect('users')->with('flash_message', 'User added!');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $user = User::findOrFail($id);

        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);

        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param  int  $id
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(UserRequest $request, $id)
    {

        $requestData = $request->all();

        $user = User::findOrFail($id);


        if ( strlen($requestData['password']) ){
            $requestData['password'] = \Hash::make( $requestData['password']);
        } else {
            unset($requestData['password']);
        }

        $requestData['api2cart_verified'] = true;

        $user->update($requestData);

        return redirect()->route('home')->with('success','User information updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy($id)
    {
        if ( $id != 1 ) {
            User::destroy($id);
        } else {
            return redirect('users')->with('flash_message', 'Cant delete main user!');
        }
        return redirect('users')->with('flash_message', 'User deleted!');
    }
}
