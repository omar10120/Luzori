<?php

namespace App\Http\Requests\CenterAPI\BookingWithTips;

use Illuminate\Foundation\Http\FormRequest;

class BookingWithTipsRequest extends FormRequest
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
            'branch_id' => 'required|exists:branches,id',
            'worker_id' => 'required|exists:workers,id',
            'date_time' => 'required',
            'tip' => 'required|numeric|between:0,200',
            'payment_type' => 'required',
        ];
    }
}
