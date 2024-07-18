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
            'barcode' => 'sometimes:required',
            'unit' => 'required',
            'product_category_id' => 'required',
            'usage_type' => 'required',
            'markup_price' => '',
            'base_price' => 'required',
            'sale_price' => 'required',
            'discount_rate' => 'required',
            'discount_type' => 'required|in:none,flat,percentage',
            'tax_rate' => 'required',
            'tax_type' => 'required|in:none,flat,percentage',
        ];
    }
}
