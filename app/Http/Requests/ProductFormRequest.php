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
            'barcode' => [Rule::unique('products', 'barcode')->ignore($productId)],
            'unit' => 'required',
            'product_category_id' => 'required',
            'base_price' => 'required',
            'product_type' => '',
            'markup_price' => '',
            'sale_price' => '',
            'discount_price' => '',
            'in_store' => 'required',
        ];
    }
}
