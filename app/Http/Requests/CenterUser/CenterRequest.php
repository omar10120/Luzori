<?php

namespace App\Http\Requests\CenterUser;

use Illuminate\Foundation\Http\FormRequest;

class CenterRequest extends FormRequest
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
        return [
            'id' => 'required|exists:centers,id',
            'name' => 'required',
            'email' => 'required|email',
            'phone' => 'nullable|numeric|digits_between:6,10',
            'image' => 'nullable|image|max:4096|mimes:jpg,jpeg,png,gif|mimetypes:image/jpeg,image/png',
        ];
    }
}
