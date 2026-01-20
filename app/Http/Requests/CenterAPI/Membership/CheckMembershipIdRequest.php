<?php

namespace App\Http\Requests\CenterAPI\Membership;

use Illuminate\Foundation\Http\FormRequest;

class CheckMembershipIdRequest extends FormRequest
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
            'id' => 'required|exists:memberships_cards,id,deleted_at,NULL'
        ];
    }
}
