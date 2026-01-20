<?php

namespace App\Http\Requests\CenterAPI\BuyProduct;

use Illuminate\Foundation\Http\FormRequest;

class CheckBuyProductIdRequest extends FormRequest
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
            'id' => 'required|exists:buy_products,id,deleted_at,NULL'
        ];
    }
}
