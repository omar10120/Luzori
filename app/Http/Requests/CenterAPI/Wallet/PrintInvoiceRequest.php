<?php

namespace App\Http\Requests\CenterAPI\Wallet;

use Illuminate\Foundation\Http\FormRequest;

class PrintInvoiceRequest extends FormRequest
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
        ];
    }
}
