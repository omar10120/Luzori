<?php

namespace App\Http\Requests\CenterAPI\CenterUser;

use App\Rules\GlobalEmailUnique;
use Illuminate\Foundation\Http\FormRequest;

class CenterUserRequest extends FormRequest
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
                'id' => 'required|exists:center_users,id,deleted_at,NULL',
                'name' => 'required|string',
                'email' => ['required', 'email', new GlobalEmailUnique($this->id, 'center_users')],
                'branch_id' => 'required|exists:branches,id',
                'country_code' => 'required|string',
                'phone' => 'required|numeric|digits_between:6,10|unique:center_users,phone,' . $this->id,
                'role' => 'required|exists:roles,name',
                'image' => 'nullable|image|max:4096|mimes:jpg,jpeg,png,gif|mimetypes:image/jpeg,image/png',
            ];
        } else {
            return [
                'name' => 'required',
                'email' => ['required', 'email', new GlobalEmailUnique()],
                'branch_id' => 'required|exists:branches,id',
                'country_code' => 'required|string',
                'phone' => 'required|numeric|digits_between:6,10|unique:center_users',
                'password' => 'required|min:6|max:15|same:password_confirmation',
                'role' => 'required|exists:roles,name',
                'image' => 'nullable|image|max:4096|mimes:jpg,jpeg,png,gif|mimetypes:image/jpeg,image/png',
            ];
        }
    }
}
