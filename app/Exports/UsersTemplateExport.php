<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UsersTemplateExport implements FromCollection, WithHeadings
{
    public function collection(): Collection
    {
        return collect();
    }

    public function headings(): array
    {
        return [
            'name', 'email', 'phone', 'company',
            'gender', 'race', 'race_sub_group', 'date_of_birth', 'place_of_birth', 'ic_no',
            'postal_address', 'residential_address', 'occupation', 'position', 'employer_name',
            'employer_address', 'employment_date', 'bank_name', 'bank_branch', 'bank_address',
            'office_tel', 'office_fax', 'present_salary', 'salary_increment_date',
            'proposed_by_name', 'seconded_by_name',
        ];
    }
}
