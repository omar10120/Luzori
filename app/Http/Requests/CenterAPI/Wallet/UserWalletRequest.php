<?php

namespace App\Http\Requests\CenterAPI\Wallet;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserWalletRequest extends FormRequest
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
            'wallet_id' => 'required|exists:wallets,id',
            'user_id' => 'required|exists:users,id',
            'wallet_type' => 'required|string',
            'worker_id' => 'nullable|exists:workers,id',
            'commission' => Rule::requiredIf($this->worker_id != ''),
        ];
    }
}
