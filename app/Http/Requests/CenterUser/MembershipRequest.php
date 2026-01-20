<?php

namespace App\Http\Requests\CenterUser;

use Illuminate\Foundation\Http\FormRequest;

class MembershipRequest extends FormRequest
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
                'id' => 'required|exists:memberships,id',
                'user_id' => 'required|exists:users,id',
                'membership_no' => 'required|string',
                'percent' => 'required|numeric|between:1,100',
                'start_at'=> 'required|date',
                'end_at' => 'required|date|after:start_at',
            ];
        } else {
            return [
                'user_id' => 'required|exists:users,id',
                'membership_no' => 'required|string',
                'percent' => 'required|numeric|between:1,100',
                'start_at'=> 'required|date',
                'end_at' => 'required|date|after:start_at',
            ];
        }
    }
}
