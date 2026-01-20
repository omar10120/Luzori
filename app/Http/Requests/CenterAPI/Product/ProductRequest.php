<?php

namespace App\Http\Requests\CenterAPI\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Config;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the admin is authorized to make this request.
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
                'id' => 'required|exists:products,id,deleted_at,NULL',
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
                'current_stock' => 'nullable|integer|min:0|required_if:track_stock,1',
                'image' => 'nullable|image|max:4096|mimes:jpg,jpeg,png,gif|mimetypes:image/jpeg,image/png',
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
                'current_stock' => 'nullable|integer|min:0|required_if:track_stock,1',
                'image' => 'required|image|max:4096|mimes:jpg,jpeg,png,gif|mimetypes:image/jpeg,image/png',
            ];
        }
        return array_merge($locales, $validations);
    }
}
