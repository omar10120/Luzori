<?php

namespace App\Http\Requests\CenterUser;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentMethodRequest extends FormRequest
{
    /**
     * Determine if the center is authorized to make this request.
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
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('payment_methods')->ignore($this->id)->where(function ($query) {
                    $query->whereNull('deleted_at'); // Only check against non-soft-deleted records
                }),
            ],
            'types' => [
                'required',
                'array',
                'min:1'
            ],
            'types.*' => [
                'string',
                Rule::in(['booking', 'product', 'wallet', 'tips', 'general'])
            ],
        ];

        if (isset($this->id)) {
            $rules['id'] = 'required|exists:payment_methods,id';
        }

        return $rules;
    }
}
