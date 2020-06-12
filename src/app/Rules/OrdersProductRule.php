<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Log;

class OrdersProductRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $result             = true;
        $products_id        = request()->get('product_id');
        $products_quantity  = request()->get('product_quantity');

        foreach ( request()->get('checked_id') as $sid ){
            $cid = array_search( $sid, $products_id );
            if ( !isset( $products_quantity[$cid] ) || intval($products_quantity[$cid]) < 1 ) $result = false;
        }

        return $result;



    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Please change quantity to be at least 1.';
    }
}
