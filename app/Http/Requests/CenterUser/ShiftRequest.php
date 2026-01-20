<?php

namespace App\Http\Requests\CenterUser;

use Illuminate\Foundation\Http\FormRequest;

class ShiftRequest extends FormRequest
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
        if (isset($this->id)) {
            return [
                'id' => 'required|exists:shifts,id',
                'name' => 'required|string',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i|after:start_time',
                'break_start' => 'required|date_format:H:i',
                'break_end' => 'required|date_format:H:i|after:break_start',
            ];
        } else {
            return [
                'name' => 'required|string',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i|after:start_time',
                'break_start' => 'required|date_format:H:i',
                'break_end' => 'required|date_format:H:i|after:break_start',
            ];
        }
    }
}
