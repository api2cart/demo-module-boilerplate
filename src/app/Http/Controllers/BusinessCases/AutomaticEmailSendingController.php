<?php

namespace App\Http\Controllers\BusinessCases;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AutomaticEmailSendingController extends CasesController
{

    public function index()
    {
        return view('business_cases.automatic_email_sending.index');
    }

}
