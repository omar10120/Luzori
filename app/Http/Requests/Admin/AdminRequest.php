<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminRequest extends FormRequest
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
                'id' => 'required|exists:admins,id',
                'name' => 'required',
                'email' => 'required|email|unique:admins,email,' . $this->id,
                'phone' => 'nullable|numeric|digits_between:6,10|unique:admins,phone,' . $this->id,
                'password' => 'nullable|min:6|max:15|same:password_confirmation',
                'password_confirmation' => 'nullable|min:6|max:15',
                'role' => 'required',
                'image' => 'nullable|image|max:4096|mimes:jpg,jpeg,png,gif|mimetypes:image/jpeg,image/png',
            ];
        } else {
            return [
                'name' => 'required',
                'email' => 'required|email|unique:admins',
                'phone' => 'nullable|numeric|digits_between:6,10|unique:admins',
                'password' => 'required|min:6|max:15|same:password_confirmation',
                'role' => 'required',
                'image' => 'required|image|max:4096|mimes:jpg,jpeg,png,gif|mimetypes:image/jpeg,image/png',
            ];
        }
    }
}
