<?php

namespace App\Http\Requests\CenterAPI\Worker;

use Illuminate\Foundation\Http\FormRequest;

class CheckWorkerIdRequest extends FormRequest
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
            'id' => 'required|exists:workers,id,deleted_at,NULL'
        ];
    }
}
