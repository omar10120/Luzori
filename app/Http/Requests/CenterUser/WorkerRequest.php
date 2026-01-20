<?php

namespace App\Http\Requests\CenterUser;

use Illuminate\Foundation\Http\FormRequest;

class WorkerRequest extends FormRequest
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
                'id' => 'required|exists:workers,id',
                'branch_id' => 'required|exists:branches,id',
                'services' => 'required|array',
                'services.*' => 'required|exists:services,id',
                'shift_id' => 'required|exists:shifts,id',
                'name' => 'required|string',
                'email' => 'nullable|email|unique:workers,email,' . $this->id,
                'phone' => 'required|numeric|digits_between:6,10|unique:workers,phone,' . $this->id,
                'image' => 'nullable|image|max:4096|mimes:jpg,jpeg,png,gif|mimetypes:image/jpeg,image/png',
            ];
        } else {
            return [
                'branch_id' => 'required|exists:branches,id',
                'services' => 'required|array',
                'services.*' => 'required|exists:services,id',
                'shift_id' => 'required|exists:shifts,id',
                'name' => 'required|string',
                'email' => 'nullable|email|unique:workers,email',
                'phone' => 'required|numeric|digits_between:6,10|unique:workers,phone',
                'image' => 'nullable|image|max:4096|mimes:jpg,jpeg,png,gif|mimetypes:image/jpeg,image/png',
            ];
        }
    }
}
