<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\Api2CartRule;


class UserRequest extends FormRequest
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
        if ( strlen($this->get('password')) || $this->get('password-confirm')){
            return [
                'name'          => ['required'],
                'api2cart_key'  => ['required', new Api2CartRule ],
                'password'      => ['required', 'string', 'min:4', 'confirmed'],
            ];
        } else {
            return [
                'name'          => ['required'],
                'api2cart_key'  => ['required', new Api2CartRule ],
            ];
        }

    }

    public function messages()
    {
        return [
            'api2cart_key.required' => "API key is required."
        ];//'Please enter valid API key.';
    }

}
