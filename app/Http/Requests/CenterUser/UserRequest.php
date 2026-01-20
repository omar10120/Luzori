<?php

namespace App\Http\Requests\CenterUser;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
                'id' => 'required|exists:users,id',
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'email' => 'nullable|email|unique:users,email,' . $this->id,
                'country_code' => 'required|max:4',
                'phone' => 'required|numeric|digits_between:6,10|unique:users,phone,' . $this->id,
                'image' => 'nullable|image|min:10|max:4096|mimes:jpg,jpeg,png,gif|mimetypes:image/jpeg,image/png',
            ];
        } else {
            return [
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'email' => 'nullable|email|unique:users',
                'country_code' => 'required|max:4',
                'phone' => 'required|numeric|digits_between:6,10|unique:users',
                'image' => 'nullable|image|min:10|max:4096|mimes:jpg,jpeg,png,gif|mimetypes:image/jpeg,image/png',
            ];
        }
    }
}
