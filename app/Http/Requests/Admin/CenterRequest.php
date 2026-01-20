<?php

namespace App\Http\Requests\Admin;

use App\Rules\GlobalEmailUnique;
use Illuminate\Foundation\Http\FormRequest;

class CenterRequest extends FormRequest
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
                'id' => 'required|exists:centers,id',
                'name' => 'required',
                'domain' => 'required|string|unique:centers,domain,' . $this->id,
                'email' => ['required', 'email', new GlobalEmailUnique($this->id, 'centers')],
                'country_code' => 'required|string',
                'phone' => 'required|numeric|digits_between:6,10|unique:centers,phone,' . $this->id,
                'currency' => 'nullable|string|max:10',
                'password' => 'nullable|min:6|max:15|same:password_confirmation',
                'password_confirmation' => 'nullable|min:6|max:15',
                'role' => 'required',
                'image' => 'nullable|image|max:4096|mimes:jpg,jpeg,png,gif|mimetypes:image/jpeg,image/png',
            ];
        } else {
            return [
                'name' => 'required',
                'domain' => 'required|string|unique:centers',
                'email' => ['required', 'email', new GlobalEmailUnique()],
                'country_code' => 'required|string',
                'phone' => 'required|numeric|digits_between:6,10|unique:centers',
                'currency' => 'nullable|string|max:10',
                'password' => 'required|min:6|max:15|same:password_confirmation',
                'role' => 'required',
                'image' => 'required|image|max:4096|mimes:jpg,jpeg,png,gif|mimetypes:image/jpeg,image/png',
            ];
        }
    }
}
