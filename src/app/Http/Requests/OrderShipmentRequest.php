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
/**
 * Date: 28.06.23 16:23
 * @category
 * @package
 * @author   Taras Kubiv <t.kubiv@magneticone.com>
 * @license  Not public license
 * @link     https://www.api2cart.com
 */

namespace App\Http\Requests;

use App\Rules\OrderShipmentRule;
use Illuminate\Foundation\Http\FormRequest;

class OrderShipmentRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   *
   * @return bool
   */
  public function authorize()
  {
    return true;
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array
   */
  public function rules()
  {
    return [
      'carrier_id' => ['required'],
      'order_id' => ['required'],
      'store_key' => ['required'],
      'items' => [new OrderShipmentRule()]
    ];
  }
}