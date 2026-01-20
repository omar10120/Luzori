<?php

namespace App\Http\Requests\CenterAPI\Booking;

use Illuminate\Foundation\Http\FormRequest;

class BookingRequest extends FormRequest
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
            'full_name' => 'required|string',
            'mobile' => 'required|numeric',
            // 'discount_id' => 'nullable|exists:discounts,id,deleted_at,NULL',
            'payment_type' => 'nullable|string|in:' . implode(',', get_payment_method_names()),
            'services' => 'required|array',
            'services.*' => 'required|exists:services,id,deleted_at,NULL',
            'service' => 'required|array',
            'service.*.date' => 'nullable|date',
            'service.*.worker_id' => 'required|exists:workers,id,deleted_at,NULL',
            'service.*.from_time' => 'required|date_format:H:i',
            'service.*.to_time' => 'required|date_format:H:i|after:service.*.from_time',
            'service.*.commission' => 'nullable|numeric',
            'service.*.commission_type' => 'nullable|in:percentage,fixed',
        ];
    }
}
