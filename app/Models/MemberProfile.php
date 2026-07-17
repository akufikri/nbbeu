<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id', 'gender', 'race', 'race_sub_group', 'date_of_birth', 'place_of_birth', 'ic_no',
    'postal_address', 'residential_address', 'occupation', 'position', 'employer_name',
    'employer_address', 'employment_date', 'bank_name', 'bank_branch', 'bank_address',
    'office_tel', 'office_fax', 'present_salary', 'salary_increment_date',
    'proposed_by_user_id', 'seconded_by_user_id', 'declaration_accepted_at', 'signature_path',
])]
class MemberProfile extends Model
{
    public const GENDERS = [
        'male' => 'Male',
        'female' => 'Female',
    ];

    public const RACES = [
        'malay' => 'Malay',
        'chinese' => 'Chinese',
        'indian' => 'Indian',
        'bumiputra' => 'Bumiputra',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'employment_date' => 'date',
            'salary_increment_date' => 'date',
            'declaration_accepted_at' => 'datetime',
            // PDPA-sensitive fields — encrypted at rest via Laravel's encrypted cast.
            'ic_no' => 'encrypted',
            'present_salary' => 'encrypted',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $profile) {
            if ($profile->isDirty('ic_no')) {
                $profile->ic_no_hash = $profile->ic_no ? hash('sha256', $profile->ic_no) : null;
            }
        });
    }

    public static function hashIcNo(string $icNo): string
    {
        return hash('sha256', $icNo);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function proposedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'proposed_by_user_id');
    }

    public function secondedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seconded_by_user_id');
    }

    public function maskedIcNo(): string
    {
        $ic = (string) $this->ic_no;

        return $ic === '' ? '' : str_repeat('*', max(strlen($ic) - 4, 0)).substr($ic, -4);
    }

    public function maskedSalary(): string
    {
        return 'RM ***.**';
    }
}
