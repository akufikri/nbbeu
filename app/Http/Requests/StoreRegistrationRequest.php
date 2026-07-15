<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->where(fn ($query) => $query->where('status', 'approved')),
            ],
            'phone' => ['required', 'string', 'max:20'],
            'company' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'This email is already registered as an approved member.',
        ];
    }
}
