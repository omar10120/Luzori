<?php

namespace App\Http\Requests\CenterUser;

use Illuminate\Foundation\Http\FormRequest;

class StockOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isTransfer = $this->input('source_type') === 'main_stock';

        return [
            'source_type' => 'required|in:supplier,main_stock',
            'branch_ids' => $isTransfer ? 'required|array|min:1' : 'nullable|array',
            'branch_ids.*' => 'required|exists:branches,id',
            'product_supplier_id' => $isTransfer
                ? 'nullable|exists:product_suppliers,id'
                : 'required|exists:product_suppliers,id',
            'expected_at' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.order_qty' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ];
    }
}
