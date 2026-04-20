<?php

namespace App\Http\Requests\CenterAPI\UserPackage;

use Illuminate\Foundation\Http\FormRequest;

class UserPackageRequest extends FormRequest
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
            'id' => 'sometimes|exists:users_packages,id',
            'user_id' => 'required|exists:users,id',
            'package_id' => 'required|exists:packages,id',
            'package_type' => 'required|string',
        ];
    }
}
