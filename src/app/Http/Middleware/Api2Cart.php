<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Api2Cart
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ( !Auth::guest() ){

            if ( Auth::user()->api2cart_key && Auth::user()->api2cart_verified == true ){

//                Log::debug( 'API key OK');
                return $next($request);

            } else {
                // API key do not exist or do not verified
//                Log::debug( 'API key do not exist or do not verified');
//                Log::debug( print_r(Auth::user(),1) );
                return redirect( route('users.edit', Auth::user()->id ) );
                return $next($request);
            }

        }
        return $next($request);
    }
}
