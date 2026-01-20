<?php

namespace App\Http\Requests\CenterUser;

use App\Rules\GlobalEmailUnique;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class CenterUserRequest extends FormRequest
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
                'id' => 'required|exists:center_users,id',
                'name' => 'required',
                'email' => ['nullable', 'email', new GlobalEmailUnique($this->id, 'center_users')],
                'branch_id' => 'required|exists:branches,id',
                'country_code' => 'required|string',
                'phone' => 'required|numeric|digits_between:6,10|unique:center_users,phone,' . $this->id,
                'currency' => 'nullable|string|max:10',
                'password' => 'nullable|min:6|max:15|same:password_confirmation',
                'password_confirmation' => 'nullable|min:6|max:15',
                'role' => 'required',
                'image' => 'nullable|image|max:4096|mimes:jpg,jpeg,png,gif|mimetypes:image/jpeg,image/png',
            ];
        } else {
            return [
                'name' => 'required',
                'email' => ['required', 'email', new GlobalEmailUnique()],
                'branch_id' => 'required|exists:branches,id',
                'country_code' => 'required|string',
                'phone' => 'required|numeric|digits_between:6,10|unique:center_users',
                'currency' => 'nullable|string|max:10',
                'password' => 'required|min:6|max:15|same:password_confirmation',
                'role' => 'required',
                'image' => 'required|image|max:4096|mimes:jpg,jpeg,png,gif|mimetypes:image/jpeg,image/png',
            ];
        }
    }
}
