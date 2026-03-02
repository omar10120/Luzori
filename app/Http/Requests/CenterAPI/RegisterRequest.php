<?php

namespace App\Http\Requests\CenterAPI;

use App\Rules\GlobalEmailUnique;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'domain' => 'required|string|unique:centers,domain',
            'email' => ['required', 'email', new GlobalEmailUnique()],
            'country_code' => 'required|string',
            'phone' => 'required|numeric|digits_between:6,15|unique:centers,phone',
            'password' => 'required|min:6|max:15|same:password_confirmation',
            'password_confirmation' => 'required',
            'image' => 'nullable|image|max:4096|mimes:jpg,jpeg,png,gif',
            'primary_image' => 'nullable|image|max:4096|mimes:jpg,jpeg,png,gif',
            'currency' => 'nullable|string|max:10',
        ];
    }
}
