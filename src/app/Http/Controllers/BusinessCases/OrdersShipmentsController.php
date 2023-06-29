<?php
/*-----------------------------------------------------------------------------+
 | MagneticOne                                                                  |
 | Copyright (c) 2023 MagneticOne.com <contact@magneticone.com>                 |
 | All rights reserved                                                          |
 +------------------------------------------------------------------------------+
 | PLEASE READ  THE FULL TEXT OF SOFTWARE LICENSE AGREEMENT IN THE "license.txt"|
 | FILE PROVIDED WITH THIS DISTRIBUTION. THE AGREEMENT TEXT IS ALSO AVAILABLE   |
 | AT THE FOLLOWING URL: http://www.magneticone.com/store/license.php           |
 |                                                                              |
 | THIS  AGREEMENT  EXPRESSES  THE  TERMS  AND CONDITIONS ON WHICH YOU MAY USE  |
 | THIS SOFTWARE   PROGRAM   AND  ASSOCIATED  DOCUMENTATION   THAT  MAGNETICONE |
 | (hereinafter  referred to as "THE AUTHOR") IS FURNISHING  OR MAKING          |
 | AVAILABLE TO YOU WITH  THIS  AGREEMENT  (COLLECTIVELY,  THE  "SOFTWARE").    |
 | PLEASE   REVIEW   THE  TERMS  AND   CONDITIONS  OF  THIS  LICENSE AGREEMENT  |
 | CAREFULLY   BEFORE   INSTALLING   OR  USING  THE  SOFTWARE.  BY INSTALLING,  |
 | COPYING   OR   OTHERWISE   USING   THE   SOFTWARE,  YOU  AND  YOUR  COMPANY  |
 | (COLLECTIVELY,  "YOU")  ARE  ACCEPTING  AND AGREEING  TO  THE TERMS OF THIS  |
 | LICENSE   AGREEMENT.   IF  YOU    ARE  NOT  WILLING   TO  BE  BOUND BY THIS  |
 | AGREEMENT, DO  NOT INSTALL OR USE THE SOFTWARE.  VARIOUS   COPYRIGHTS   AND  |
 | OTHER   INTELLECTUAL   PROPERTY   RIGHTS    PROTECT   THE   SOFTWARE.  THIS  |
 | AGREEMENT IS A LICENSE AGREEMENT THAT GIVES  YOU  LIMITED  RIGHTS   TO  USE  |
 | THE  SOFTWARE   AND  NOT  AN  AGREEMENT  FOR SALE OR FOR  TRANSFER OF TITLE. |
 | THE AUTHOR RETAINS ALL RIGHTS NOT EXPRESSLY GRANTED BY THIS AGREEMENT.       |
 |                                                                              |
 | The Developer of the Code is MagneticOne,                                    |
 | Copyright (C) 2006 - 2023 All Rights Reserved.                               |
 +-----------------------------------------------------------------------------*/

namespace App\Http\Controllers\BusinessCases;

use App\Http\Controllers\Controller;
use App\Jobs\MakeRequestToA2C;
use App\Services\Api2Cart;
use ShipmentsController;

/**
 * Date: 22.06.23 10:26
 * @category
 * @package
 * @author   Taras Kubiv <t.kubiv@magneticone.com>
 * @license  Not public license
 * @link     https://www.api2cart.com
 */

class OrdersShipmentsController extends Controller
{
  private $_api2cart;

 /**
  * OrdersShipmentsController constructor.
  *
  * @param Api2Cart $api2Cart Api2Cart service
  */
  public function __construct(Api2Cart $api2Cart)
  {
    $this->_api2cart = $api2Cart;
  }

/**
 * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
 */
  public function index()
  {
    return view('business_cases.orders_shipments_controller.index');
  }
}