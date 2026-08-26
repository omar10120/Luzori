<?php

namespace App\Http\Requests\CenterUser;

use Illuminate\Foundation\Http\FormRequest;

class StockOrderReceiveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:stock_order_items,id',
            'items.*.received_qty' => 'required|numeric|min:0',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ];
    }
}
