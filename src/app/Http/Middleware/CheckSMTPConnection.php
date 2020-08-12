<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class CheckSMTPConnection
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

//        Log::debug('CheckSMTPConnection');

        $mailMD5 = md5( md5(env('MAIL_HOST')) . md5(env('MAIL_PORT')) . md5(env('MAIL_USERNAME')) . md5(env('MAIL_PASSWORD')) . md5(env('MAIL_ENCRYPTION')) );

        if ( session()->get('mail_md5') === $mailMD5 ) return $next($request);

        if (config('app.env') == 'testing') return $next($request);



        try{


            $this->checkSMTP();
            session()->put('mail_md5', $mailMD5);


        } catch (\Swift_TransportException $e){

//            Log::debug( $e->getMessage() );
//            Log::debug(' looks mail not setuped correctly ');

            // redirect on screen with mail setting instructions
            return redirect( '/mail_settings' );


        } catch (\Exception $e){
            Log::debug( $e->getMessage() );
        }

        return $next($request);
    }


    public function checkSMTP()
    {
        $mailer = env('MAIL_DRIVER','smtp');
        $s = Mail::createTransport( config("mail.mailers.{$mailer}") );
        $s->start();

        Log::debug( 'checked smtp - ok' );

        return 'ok';




    }



}
