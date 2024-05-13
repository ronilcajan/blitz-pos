<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDeliveryRequest extends FormRequest
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
        return [
            'supplier_id' => 'required',
            'transaction_date' => 'required|date',
            'quantity' => 'required|numeric',
            'amount' => 'required|numeric',
            'total' => 'required|numeric',
            'items.id.*' => 'required',
            'items.price.*' => 'required|numeric',
            'items.quantity.*' => 'required|numeric',
        ];
    }
}
