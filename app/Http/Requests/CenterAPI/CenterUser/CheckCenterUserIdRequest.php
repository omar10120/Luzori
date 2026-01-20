<?php

namespace App\Http\Requests\CenterAPI\CenterUser;

use Illuminate\Foundation\Http\FormRequest;

class CheckCenterUserIdRequest extends FormRequest
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
            'id' => 'required|exists:center_users,id,deleted_at,NULL'
        ];
    }
}
