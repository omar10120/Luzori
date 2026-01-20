<?php

namespace App\Http\Requests\CenterAPI\Worker;

use Illuminate\Foundation\Http\FormRequest;

class WorkerRequest extends FormRequest
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
                'id' => 'required|exists:workers,id,deleted_at,NULL',
                'branch_id' => 'required|exists:branches,id,deleted_at,NULL',
                'services' => 'required|array',
                'services.*' => 'required|exists:services,id,deleted_at,NULL',
                'shift_id' => 'required|exists:shifts,id,deleted_at,NULL',
                'name' => 'required|string',
                'email' => 'nullable|email|unique:workers,email,' . $this->id,
                'country_code' => 'required|string',
                'phone' => 'required|numeric|digits_between:6,10|unique:workers,phone,' . $this->id,
                'image' => 'nullable|image|max:4096|mimes:jpg,jpeg,png,gif|mimetypes:image/jpeg,image/png',
            ];
        } else {
            return [
                'branch_id' => 'required|exists:branches,id,deleted_at,NULL',
                'services' => 'required|array',
                'services.*' => 'required|exists:services,id,deleted_at,NULL',
                'shift_id' => 'required|exists:shifts,id,deleted_at,NULL',
                'name' => 'required|string',
                'email' => 'nullable|email|unique:workers,email',
                'country_code' => 'required|string',
                'phone' => 'required|numeric|digits_between:6,10|unique:workers,phone',
                'image' => 'nullable|image|max:4096|mimes:jpg,jpeg,png,gif|mimetypes:image/jpeg,image/png',
            ];
        }
    }
}
