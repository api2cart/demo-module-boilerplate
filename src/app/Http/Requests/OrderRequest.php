<?php

namespace App\Http\Requests;

use App\Rules\OrdersProductRule;
use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
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
            'cart_id'       => ['required'],
            'customer_id'   => ['required'],
            'status_id'     => ['required'],
            'checked_id'    => ['required', new OrdersProductRule ]
        ];
    }
}
