<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFormRequest extends FormRequest
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
            'name' => 'required',
            'email' => 'required|email',
            'founder' => '',
            'tagline' => '',
            'contact' => '',
            'website' => '',
            'industry' => '',
            'country' => '',
            'country_code' => '',
            'timezone' => '',
            'currency' => '',
            'currency_symbol' => '',
            'description' => '',
            'address' => '',
        ];
    }
}
