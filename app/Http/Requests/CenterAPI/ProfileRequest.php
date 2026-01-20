<?php

namespace App\Http\Requests\CenterAPI;

use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
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
            'name' => 'required|string',
            'email' => 'required|email|unique:center_users,email,' . auth('center_api')->user()->id,
            'country_code' => 'required|string',
            'phone' => 'required|numeric|digits_between:6,10|unique:center_users,phone,' . auth('center_api')->user()->id,
            'image' => 'nullable|image|max:4096|mimes:jpg,jpeg,png,gif|mimetypes:image/jpeg,image/png',
        ];
    }
}
