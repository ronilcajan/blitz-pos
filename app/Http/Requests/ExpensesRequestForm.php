<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExpensesRequestForm extends FormRequest
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
            'expenses_date' => 'required',
            'vendor' => 'required',
            'amount' => 'required',
            'notes' => '',
            'status' => '',
            'expenses_category_id' => 'required',
            'store_id' => 'required',
        ];
    }
}
