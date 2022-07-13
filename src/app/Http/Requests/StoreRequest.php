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
        $rules = [
            'cart_id'   => ['required'],
            'field.*'   => ['required'],
            'field.store_url' => [
                'required',
                'regex:/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/'
            ]
        ];

        $fields = request()->all();

        if (isset($fields['field']['store_key'])) {
            $rules['field.store_key'] = [
                'required',
                'regex:/^[0-9a-z]{32}$/'
            ];
        }

        if (isset($fields['field']['multicred'])) {
            $filledSet = null;

            foreach ($fields['field']['multicred'] as $key => $field) {
                foreach ($field as $fieldValue) {
                    if ($fieldValue !== null) {
                        $filledSet = $key;

                        break 2;
                    }
                }
            }

            foreach ($fields['field']['multicred'] as $key => $fields) {
                if ($filledSet !== null && $filledSet !== $key) {
                    continue;
                }

                foreach ($fields as $fieldName => $fieldValue) {
                    $rules['field.multicred.' . $key . '.' . $fieldName] = ['required'];

                    if ($filledSet === null) {
                        break;
                    }
                }
            }
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'required'  => 'The field is required.'
        ];
    }

}
