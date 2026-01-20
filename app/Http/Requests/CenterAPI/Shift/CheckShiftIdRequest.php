<?php

namespace App\Http\Requests\CenterAPI\Shift;

use Illuminate\Foundation\Http\FormRequest;

class CheckShiftIdRequest extends FormRequest
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
            'id' => 'required|exists:shifts,id,deleted_at,NULL'
        ];
    }
}
