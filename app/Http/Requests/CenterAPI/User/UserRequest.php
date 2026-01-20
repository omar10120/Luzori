<?php

namespace App\Http\Requests\CenterAPI\User;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
        if (isset($this->id)) {
            return [
                'id' => 'required|exists:users,id,deleted_at,NULL',
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'email' => 'nullable|email|unique:users,email,' . $this->id,
                'country_code' => 'required|string',
                'phone' => 'required|numeric|digits_between:6,10|unique:users,phone,' . $this->id,
                'image' => 'nullable|image|max:4096|mimes:jpg,jpeg,png,gif|mimetypes:image/jpeg,image/png',
            ];
        } else {
            return [
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'email' => 'nullable|email|unique:users',
                'country_code' => 'required|string',
                'phone' => 'required|numeric|digits_between:6,10|unique:users',
                'image' => 'nullable|image|max:4096|mimes:jpg,jpeg,png,gif|mimetypes:image/jpeg,image/png',
            ];
        }
    }
}
