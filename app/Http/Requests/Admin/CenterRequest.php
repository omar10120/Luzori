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
                'primary_image' => 'nullable|array|max:4',
                'primary_image.*' => 'image|max:4096|mimes:jpg,jpeg,png,gif|mimetypes:image/jpeg,image/png',
                'delete_primary_images' => 'nullable|string',
                'status' => 'nullable|in:pending,approve,reject',
                'reject_reason' => 'nullable|string',
                'rate' => 'nullable|in:recently_viewed,recommended,new_to,trending',
                'bank_name' => 'nullable|string|max:40',
                'admin_discount' => 'nullable|numeric|min:0|max:100',
                'iban' => 'nullable|string',
                'BankAccountHolderName' => 'nullable|string',
                'BusinessName' => 'nullable|string',
                'BankAccount' => 'nullable|string',
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
                'image' => 'nullable|image|max:4096|mimes:jpg,jpeg,png,gif|mimetypes:image/jpeg,image/png',
                'primary_image' => 'nullable|array|max:4',
                'primary_image.*' => 'image|max:4096|mimes:jpg,jpeg,png,gif|mimetypes:image/jpeg,image/png',
                'delete_primary_images' => 'nullable|string',
                'status' => 'nullable|in:pending,approve,reject',
                'reject_reason' => 'nullable|string',
                'rate' => 'nullable|in:recently_viewed,recommended,new_to,trending',
                'bank_name' => 'nullable|string|max:40',
                'admin_discount' => 'nullable|numeric|min:0|max:100',
                'iban' => 'nullable|string',
                'BankAccountHolderName' => 'nullable|string',
                'BusinessName' => 'nullable|string',
                'BankAccount' => 'nullable|string',

            ];
        }
    }
}
