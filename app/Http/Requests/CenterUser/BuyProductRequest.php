<?php

namespace App\Http\Requests\CenterUser;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BuyProductRequest extends FormRequest
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
        return [
            'products' => 'required|array',
            'products.*' => 'required|exists:products,id',
            'payment_type' => 'required|string|in:' . implode(',', get_payment_method_names('product')),
            'discount' => 'nullable|numeric',
            'sales_worker_id' => 'required|exists:workers,id',
            'worker_id' => 'nullable|exists:workers,id',
            'commission' => Rule::requiredIf($this->worker_id != ''),
        ];
    }
}
