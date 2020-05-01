<?php

namespace App\Rules;

use App\Services\Api2Cart;
use Illuminate\Contracts\Validation\Rule;

class Api2CartRule implements Rule
{
    private $api2cart;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {

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
        $this->api2cart = new Api2Cart;

        return $this->api2cart->checkConnection($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Please enter valid API key.';
    }
}
