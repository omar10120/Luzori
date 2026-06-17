<?php

namespace App\Http\Requests\CenterAPI;

use App\Rules\GlobalEmailUnique;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required','string','max:255'],

            'domain' => [
                'required',
                'string',
                'min:3',
                'max:50',
                'regex:/^(?!-)[a-z0-9-]+(?<!-)$/', // no spaces, no start/end hyphen
                'unique:centers,domain'
            ],

            'email' => ['required','email', new GlobalEmailUnique()],

            'country_code' => ['required','string','max:5'],

            'phone' => [
                'required',
                'numeric',
                'digits_between:6,15',
                'unique:centers,phone'
            ],

            'password' => [
                'required',
                'string',
                'min:6',
                'max:15',
                'confirmed'
            ],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,gif',
                'max:4096'
            ],

            'primary_image' => [
                'nullable',
                'array',
                'max:4'
            ],

            'primary_image.*' => [
                'image',
                'mimes:jpg,jpeg,png,gif',
                'max:4096'
            ],

            'currency' => [
                'nullable',
                'string',
                'max:10'
            ],

            'bank_name' => [
                'nullable',
                'string',
                'max:21',
            ],
            
        ];
    }

    public function messages(): array
    {
        return [
            'domain.regex' => 'The domain may only contain lowercase letters, numbers, and hyphens. Spaces are not allowed.',
            'domain.unique' => 'This domain is already taken.',
            'password.confirmed' => 'Password confirmation does not match.',
            'phone.unique' => 'This phone number is already registered.',
        ];
    }
}