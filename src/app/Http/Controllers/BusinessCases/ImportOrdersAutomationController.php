<?php

namespace App\Http\Controllers\BusinessCases;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ImportOrdersAutomationController extends CasesController
{

    public function index()
    {
        return view('business_cases.import_orders_automation.index');
    }


}
