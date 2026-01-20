<?php

namespace App\Http\Requests\CenterAPI\WeekDay;

use Illuminate\Foundation\Http\FormRequest;

class WeekDayRequest extends FormRequest
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
            'id' => 'required|exists:weeks_days,id',
            'status' => 'required|boolean'
        ];
    }
}
