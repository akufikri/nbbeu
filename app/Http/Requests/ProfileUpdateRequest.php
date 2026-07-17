<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Members may only update their name, phone number, and profile photo
     * (used on the member card) — email/company and the legal
     * member_profiles fields (IC, address, employer, etc.) are locked after
     * registration per FEATURES.md §11.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
