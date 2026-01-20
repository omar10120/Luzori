<?php

namespace App\Http\Requests\CenterAPI\Discount;

use Illuminate\Foundation\Http\FormRequest;

class CheckDiscountIdRequest extends FormRequest
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
            'id' => 'required|exists:discounts,id,deleted_at,NULL'
        ];
    }
}
