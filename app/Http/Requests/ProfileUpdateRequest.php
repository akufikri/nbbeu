<?php

namespace App\Http\Requests;

use App\Models\MemberProfile;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'photo' => ['nullable', 'image', 'max:2048'],

            'gender' => ['nullable', 'string', 'in:'.implode(',', array_keys(MemberProfile::GENDERS))],
            'race' => ['nullable', 'string', 'in:'.implode(',', array_keys(MemberProfile::RACES))],
            'race_sub_group' => ['nullable', 'string', 'max:50'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'place_of_birth' => ['nullable', 'string', 'max:255'],
            'ic_no' => [
                'nullable', 'string', 'max:255',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $exists = MemberProfile::where('ic_no_hash', MemberProfile::hashIcNo($value))
                        ->where('user_id', '!=', $this->user()->id)
                        ->exists();

                    if ($exists) {
                        $fail('This IC number is already registered.');
                    }
                },
            ],
            'postal_address' => ['nullable', 'string'],
            'residential_address' => ['nullable', 'string'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'employer_name' => ['nullable', 'string', 'max:255'],
            'employer_address' => ['nullable', 'string'],
            'employment_date' => ['nullable', 'date'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_branch' => ['nullable', 'string', 'max:255'],
            'bank_address' => ['nullable', 'string'],
            'office_tel' => ['nullable', 'string', 'max:20'],
            'office_fax' => ['nullable', 'string', 'max:20'],
            'present_salary' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
