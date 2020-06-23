<?php

namespace App\Http\Requests;

use App\Rules\Api2CartStoreFields;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class StoreRequest extends FormRequest
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
//        Log::debug( print_r( request()->all(),1) );

        return [
            'cart_id'   => ['required'],
            'field.*'   => ['required']
        ];
    }

    public function messages()
    {
        return [
            'required'  => 'The field is required.'
        ];
    }

}
