<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SupplierRequestForm extends FormRequest
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
        $supplierId = $this->id;

        return [
            'name' => 'required',
            'contact_person' => 'required',
            'email' => ['required',Rule::unique('supplier', 'email')->ignore($supplierId)],
            'phone' => '',
            'address' => '',
            'store_id' => 'required',
        ];
    }
}
