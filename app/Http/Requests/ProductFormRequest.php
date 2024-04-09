<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $productId = $this->id;
        return [
            'name' => 'required',
            'barcode' => ['required',Rule::unique('products', 'barcode')->ignore($productId)],
            'sku' => '',
            'size' => '',
            'dimension' => '',
            'unit' => '',
            'product_type' => '',
            'brand' => '',
            'manufacturer' => '',
            'description' => '',
            'product_category_id' => 'required',
            'unit_price' => '',
            'mark_up_price' => '',
            'retail_price' => '',
            'min_quantity' => '',
            'in_store' => '',
            'in_warehouse' => '',
            'supplier_id' => 'required',
        ];
    }
}
