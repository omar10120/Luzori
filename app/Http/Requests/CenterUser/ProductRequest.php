<?php

namespace App\Http\Requests\CenterUser;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Config;

class ProductRequest extends FormRequest
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
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        foreach (Config::get('translatable.locales') as $locale) {
            $this->merge([
                'name_' . $locale => $this->$locale['name'],
                'text_' . $locale => $this->$locale['text']
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $locales = [];
        foreach (Config::get('translatable.locales') as $locale) {
            $locales = array_merge($locales, [
                'name_' . $locale => 'required|string',
                'text_' . $locale => 'required|string'

            ]);
        }
        
        $productId = $this->id ?? null;
        
        if (isset($this->id)) {
            $validations = [
                'id' => 'required|exists:products,id',
                'barcode' => 'nullable|string|max:255',
                'brand_id' => 'nullable|exists:brands,id',
                'category_id' => 'nullable|exists:categories,id',
                'measure_unit' => 'nullable|string|max:50',
                'measure_amount' => 'nullable|numeric|min:0',
                'short_description' => 'nullable|string|max:100',
                'supply_price' => 'required|numeric|min:0',
                'retail_price' => 'required_if:allow_retail_sales,1|nullable|numeric|min:0',
                'markup' => 'nullable|numeric|min:0|max:100',
                'allow_retail_sales' => 'nullable|boolean',
                'skus' => 'nullable|array',
                'skus.*.sku' => 'required|string|max:100',
                'skus.*.order' => 'nullable|integer|min:0',
                'product_supplier_ids' => 'nullable|array',
                'product_supplier_ids.*' => 'exists:product_suppliers,id',
                'track_stock' => 'nullable|boolean',
                'product_branches' => 'required|array|min:1',
                'product_branches.*.branch_id' => 'required_with:product_branches.*.stock_quantity|exists:branches,id',
                'product_branches.*.stock_quantity' => 'required_with:product_branches.*.branch_id|nullable|integer|min:0',
                'image' => 'nullable|array',
                'image.*' => 'image|mimes:jpg,jpeg,png,gif|max:4096',
                'image_order' => 'nullable|string',
                'deleted_images' => 'nullable|string',
                'main_image_id' => 'nullable|string',
            ];
        } else {
            $validations = [
                'barcode' => 'nullable|string|max:255',
                'brand_id' => 'nullable|exists:brands,id',
                'category_id' => 'nullable|exists:categories,id',
                'measure_unit' => 'nullable|string|max:50',
                'measure_amount' => 'nullable|numeric|min:0',
                'short_description' => 'nullable|string|max:100',
                'supply_price' => 'required|numeric|min:0',
                'retail_price' => 'required_if:allow_retail_sales,1|nullable|numeric|min:0',
                'markup' => 'nullable|numeric|min:0|max:100',
                'allow_retail_sales' => 'nullable|boolean',
                'skus' => 'nullable|array',
                'skus.*.sku' => 'required|string|max:100',
                'skus.*.order' => 'nullable|integer|min:0',
                'product_supplier_ids' => 'nullable|array',
                'product_supplier_ids.*' => 'exists:product_suppliers,id',
                'track_stock' => 'nullable|boolean',
                'product_branches' => 'required|array|min:1',
                'product_branches.*.branch_id' => 'required_with:product_branches.*.stock_quantity|exists:branches,id',
                'product_branches.*.stock_quantity' => 'required_with:product_branches.*.branch_id|nullable|integer|min:0',
                'image' => 'nullable|array',
                'image.*' => 'image|mimes:jpg,jpeg,png,gif|max:4096',
                'image_order' => 'nullable|string',
                'deleted_images' => 'nullable|string',
                'main_image_id' => 'nullable|string',
            ];
        }
        return array_merge($locales, $validations);
    }
}
