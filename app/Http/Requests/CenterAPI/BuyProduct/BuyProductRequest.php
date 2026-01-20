<?php

namespace App\Http\Requests\CenterAPI\BuyProduct;

use Illuminate\Foundation\Http\FormRequest;

class BuyProductRequest extends FormRequest
{
    /**
     * Determine if the admin is authorized to make this request.
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
            'products' => 'required|array',
            'products.*' => 'required|exists:products,id,deleted_at,NULL',
            'payment_type' => 'required|string|in:' . implode(',', get_payment_method_names()),
            'commission' => 'nullable|numeric',
            'discount' => 'nullable|numeric',
            'sales_worker_id' => 'required|exists:workers,id,deleted_at,NULL',
            'worker_id' => 'nullable|exists:workers,id,deleted_at,NULL',
        ];
    }
}
