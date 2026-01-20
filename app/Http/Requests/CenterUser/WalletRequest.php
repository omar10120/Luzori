<?php

namespace App\Http\Requests\CenterUser;

use Illuminate\Foundation\Http\FormRequest;

class WalletRequest extends FormRequest
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
                'id' => 'required|exists:wallets,id',
                'amount' => 'required|numeric',
                'invoiced_amount'=>'required|numeric',
                'start_at' => 'required|date',
                'end_at' => 'required|date|after:start_at',
            ];
        } else {
            return [
                'amount' => 'required|numeric',
                'invoiced_amount'=>'required|numeric',
                'start_at' => 'required|date',
                'end_at' => 'required|date|after:start_at',
            ];
        }
    }
}
