<?php

namespace App\Http\Requests\CenterAPI\Discount;

use Illuminate\Foundation\Http\FormRequest;

class DiscountRequest extends FormRequest
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
        if (isset($this->id)) {
            return [
                'id' => 'required|exists:discounts,id,deleted_at,NULL',
                'type' => 'required|string|in:fixed,percentage',
                'using_type' => 'required|string|in:single,multi',
                'benefit_numbers' => 'required_if:using_type,multi',
                'amount' => 'required|numeric',
                'start_at'=> 'required|date',
                'end_at' => 'required|after:start_at',
            ];
        } else {
            return [
                'type' => 'required|string|in:fixed,percentage',
                'using_type' => 'required|string|in:single,multi',
                'benefit_numbers' => 'required_if:using_type,multi',
                'amount' => 'required|numeric',
                'start_at'=> 'required|date',
                'end_at' => 'required|after:start_at',
            ];
        }
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->sometimes('amount', 'between:1,100', function ($input) {
            return $input->type === 'percentage';
        });

        $validator->sometimes('amount', 'numeric', function ($input) {
            return $input->type === 'fixed';
        });
    }
}
