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
 * Date: 28.06.23 16:21
 * @category
 * @package
 * @author   Taras Kubiv <t.kubiv@magneticone.com>
 * @license  Not public license
 * @link     https://www.api2cart.com
 */

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class OrderShipmentRule implements Rule
{

  /**
   * Determine if the validation rule passes.
   *
   * @param string $attribute Attribute
   * @param mixed  $value     Value
   *
   * @return bool
   */
  public function passes($attribute, $value)
  {
    if ($items = request()->get('items')) {
      foreach ($items as $quantity) {
        if ($quantity <= 0) {
          return false;
        }
      }
    }

    return true;
  }

  /**
   * Get the validation error message.
   *
   * @return string|array
   */
  public function message()
  {
    return 'Please change quantity to be at least 1.';
  }
}