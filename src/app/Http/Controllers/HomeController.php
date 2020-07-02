<?php

namespace App\Http\Controllers;

use App\Services\Api2Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Brotzka\DotenvEditor\DotenvEditor;

class HomeController extends Controller
{
    private $api2cart;


    public function __construct(Api2Cart $api2Cart)
    {
        $this->middleware('auth');
        $this->api2cart = $api2Cart;

    }


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
//        dd( \Auth::user() );

//        dd( $this->api2cart->checkConnection() );

        return view('dashboard');
    }

    public function test(Request $request){

//        \Config::set('dotenveditor.pathToEnv', '/var/www/html/.env.example');

        $env = new DotenvEditor();

        $env->changeEnv([
            'MAIL_HOST'     => $request->get('MAIL_HOST'),
            'MAIL_PORT'     => $request->get('MAIL_PORT'),
            'MAIL_USERNAME' => $request->get('MAIL_USERNAME'),
            'MAIL_PASSWORD' => $request->get('MAIL_PASSWORD'),
            'MAIL_ENCRYPTION' => $request->get('MAIL_ENCRYPTION')
        ]);

        \Artisan::call('cache:clear');

        return redirect( route('test_mail_settings') );

    }

    public function checkSMTP()
    {

        try{
            $env = new DotenvEditor();

            $mailer = env('MAIL_DRIVER');
            $s = Mail::createTransport( [
                'transport' => 'smtp',
                'host' => $env->getValue('MAIL_HOST'),
                'port' => $env->getValue('MAIL_PORT'),
                'encryption' => $env->getValue('MAIL_ENCRYPTION'),
                'username' => $env->getValue('MAIL_USERNAME'),
                'password' => $env->getValue('MAIL_PASSWORD'),
            ] );
            $s->start();

            Log::debug( 'smtp - ok');

            return redirect( route('home') );

        } catch (\Swift_TransportException $e){

            // redirect on screen with mail setting instructions
            Log::debug( $e->getMessage() );


        } catch (\Exception $e){
            Log::debug( $e->getMessage() );
        }

        return view( 'email_settings' )->with('error','Mail settings is incorrect. Please check and try again.');


    }

}
