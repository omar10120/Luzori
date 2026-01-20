<?php

namespace App\Http\Requests\CenterUser;

use Illuminate\Foundation\Http\FormRequest;

class BookingRequest extends FormRequest
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
            'full_name' => 'required|string',
            'mobile' => 'required|numeric',
            // 'discount_id' => 'nullable|exists:discounts,id',
            
            'payment_type' => 'nullable|string|in:' . implode(',', get_payment_method_names('booking')),
            'services' => 'required|array',
            'services.*' => 'required|exists:services,id',
            'service' => 'required|array',
            'service.*.date' => 'required|date',
            'service.*.worker_id' => 'required|exists:workers,id',
            'service.*.from_time' => 'required|date_format:H:i',
            'service.*.to_time' => 'required|date_format:H:i|after:service.*.from_time',
            'service.*.commission' => 'nullable|numeric',
            'service.*.commission_type' => 'nullable|in:percentage,fixed',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (isset($this->service)) {
                foreach ($this->service as $serviceId => $serviceData) {
                    if (isset($serviceData['commission_type']) && $serviceData['commission_type'] === 'fixed') {
                        $service = \App\Models\Service::find($serviceId);
                        if ($service && isset($serviceData['commission'])) {
                            $commissionValue = floatval($serviceData['commission']);
                            $servicePrice = floatval($service->price);
                            
                            if ($commissionValue > $servicePrice) {
                                $validator->errors()->add(
                                    "service.{$serviceId}.commission",
                                    __('field.commission_cannot_exceed_service_price') . '. ' . __('field.service_price') . ': ' . get_num_format($servicePrice) . ' ' . trim(get_currency())
                                );
                            }
                        }
                    }
                }
            }
        });
    }
}
