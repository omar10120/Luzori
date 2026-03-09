<?php

namespace App\Http\Requests\CenterAPI;

use Illuminate\Foundation\Http\FormRequest;

class CheckCodeRequest extends FormRequest
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
        $type = (preg_match("/^[^@]*@[^@]*\.[^@]*$/", $this->username)) ? 'email' : 'phone';
        if ($type == 'email') {
            return [
                'username' => 'required|email',
                'verification_code' => 'required'
            ];
        } else {
            return [
                'username' => 'required|numeric',
                'verification_code' => 'required'
            ];
        }
    }
}
