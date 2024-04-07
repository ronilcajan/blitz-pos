<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserFormRequest extends FormRequest
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
        $userId = $this->id;

        $stores_required = $this->role_id == 1 ? '' : 'required'; // if super-admin, tenant is not required

        return [
            'name' => 'required',
            'email' => ['required',Rule::unique('users', 'email')->ignore($userId)],
            'phone' => '',
            'address' => '',
            'store_id' => $stores_required,
            'role_id' => 'required',
        ];
    }
}
