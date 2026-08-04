#!/usr/bin/env bash
# Run from anywhere inside the Laravel project: bash scripts/adjust-import-export-template-fields.sh
# Lead feedback: Excel import/export template only had name/email/phone/company.
# Extend it to also read/write the full "Personal Data Form" fields (gender, race,
# date_of_birth, ic_no, addresses, employer, bank, etc.) that already exist on
# MemberProfile and are collected during registration — all optional, still only
# name/email/phone/company are required.
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP_ROOT="$SCRIPT_DIR"
while [ ! -f "$APP_ROOT/artisan" ] && [ "$APP_ROOT" != "/" ]; do
    APP_ROOT="$(dirname "$APP_ROOT")"
done
if [ ! -f "$APP_ROOT/artisan" ]; then
    echo "Could not locate artisan (looked upward from $SCRIPT_DIR). Run this from inside the Laravel project." >&2
    exit 1
fi
cd "$APP_ROOT"

# ------------------------------------------------------------
# 1) Export template — full Personal Data Form column set
# ------------------------------------------------------------
mkdir -p app/Exports
cat > app/Exports/UsersTemplateExport.php <<'PHP'
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
PHP

# ------------------------------------------------------------
# 2) Import page — read + validate the optional profile columns,
#    create MemberProfile alongside User when present
# ------------------------------------------------------------
mkdir -p app/Filament/Resources/Users/Pages
cat > app/Filament/Resources/Users/Pages/ImportUsers.php <<'PHP'
<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Imports\UsersImportReader;
use App\Models\MemberProfile;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Validator;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class ImportUsers extends Page
{
    use WithFileUploads;

    protected static string $resource = UserResource::class;

    protected string $view = 'filament.resources.users.pages.import-users';

    public $file = null;

    public bool $previewed = false;

    /** @var array<int, array<string, mixed>> */
    public array $validRows = [];

    /** @var array<int, array{row: int, reasons: array<int, string>, data: array<string, mixed>}> */
    public array $invalidRows = [];

    /** Optional Personal Data Form columns, on top of the 4 required user columns. */
    private const PROFILE_COLUMNS = [
        'gender', 'race', 'race_sub_group', 'date_of_birth', 'place_of_birth', 'ic_no',
        'postal_address', 'residential_address', 'occupation', 'position', 'employer_name',
        'employer_address', 'employment_date', 'bank_name', 'bank_branch', 'bank_address',
        'office_tel', 'office_fax', 'present_salary', 'salary_increment_date',
        'proposed_by_name', 'seconded_by_name',
    ];

    public function preview(): void
    {
        $this->validate(['file' => ['required', 'file', 'mimes:xlsx,xls,csv']]);

        $rows = Excel::toArray(new UsersImportReader, $this->file)[0] ?? [];

        $this->validRows = [];
        $this->invalidRows = [];

        $seenEmails = [];

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +1 heading row, +1 for 1-based
            $data = [
                'name' => trim((string) ($row['name'] ?? '')),
                'email' => trim((string) ($row['email'] ?? '')),
                'phone' => trim((string) ($row['phone'] ?? '')),
                'company' => trim((string) ($row['company'] ?? '')),
            ];

            foreach (self::PROFILE_COLUMNS as $column) {
                $value = trim((string) ($row[$column] ?? ''));
                $data[$column] = $value === '' ? null : $value;
            }

            $validator = Validator::make($data, [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255'],
                'phone' => ['required', 'string', 'max:20'],
                'company' => ['required', 'string', 'max:255'],
                'gender' => ['nullable', 'in:'.implode(',', array_keys(MemberProfile::GENDERS))],
                'race' => ['nullable', 'in:'.implode(',', array_keys(MemberProfile::RACES))],
                'race_sub_group' => ['nullable', 'string', 'max:255'],
                'date_of_birth' => ['nullable', 'date'],
                'place_of_birth' => ['nullable', 'string', 'max:255'],
                'ic_no' => ['nullable', 'string', 'max:30'],
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
                'present_salary' => ['nullable', 'numeric'],
                'salary_increment_date' => ['nullable', 'date'],
                'proposed_by_name' => ['nullable', 'string', 'max:255'],
                'seconded_by_name' => ['nullable', 'string', 'max:255'],
            ]);

            $reasons = $validator->fails() ? array_values($validator->errors()->all()) : [];

            if (! $reasons) {
                $emailLower = strtolower($data['email']);

                if (isset($seenEmails[$emailLower])) {
                    $reasons[] = 'Duplicate email within the file (row '.$seenEmails[$emailLower].')';
                } elseif (User::where('email', $data['email'])->where('status', 'approved')->exists()) {
                    $reasons[] = 'Email already registered as an approved member';
                } else {
                    $seenEmails[$emailLower] = $rowNumber;
                }
            }

            if ($reasons) {
                $this->invalidRows[] = ['row' => $rowNumber, 'reasons' => $reasons, 'data' => $data];
            } else {
                $this->validRows[] = $data;
            }
        }

        $this->previewed = true;
    }

    public function commit(): void
    {
        foreach ($this->validRows as $data) {
            $userData = array_intersect_key($data, array_flip(['name', 'email', 'phone', 'company']));
            $profileData = array_filter(
                array_intersect_key($data, array_flip(self::PROFILE_COLUMNS)),
                fn ($value) => $value !== null
            );

            $user = User::create($userData);
            $user->assignRole('member');

            if ($profileData) {
                $user->memberProfile()->create($profileData);
            }
        }

        Notification::make()
            ->title(count($this->validRows).' members imported, '.count($this->invalidRows).' failed')
            ->success()
            ->send();

        $this->reset(['file', 'previewed', 'validRows', 'invalidRows']);
    }
}
PHP

# ------------------------------------------------------------
# 3) Import page hint text — mention the optional columns
# ------------------------------------------------------------
mkdir -p resources/views/filament/resources/users/pages
cat > resources/views/filament/resources/users/pages/import-users.blade.php <<'BLADE'
<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">Upload Excel File</x-slot>
        <x-slot name="description">
            Required columns: name, email, phone, company. Optional Personal Data Form columns (gender, race,
            date_of_birth, ic_no, addresses, employer, bank, etc.) are also read if present — use the downloaded
            template for exact column names (first row = header).
        </x-slot>

        <form wire:submit="preview" class="space-y-4">
            <input type="file" wire:model="file" accept=".xlsx,.xls,.csv"
                   class="block w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800" />

            @error('file')
                <p class="text-sm text-danger-600">{{ $message }}</p>
            @enderror

            <x-filament::button type="submit" wire:loading.attr="disabled">
                Preview
            </x-filament::button>
        </form>
    </x-filament::section>

    @if ($previewed)
        <x-filament::section>
            <x-slot name="heading">
                Preview Results — {{ count($validRows) }} valid, {{ count($invalidRows) }} failed
            </x-slot>

            @if (count($invalidRows) > 0)
                <div class="mb-6">
                    <h3 class="font-medium text-sm text-danger-600 mb-2">Failed Rows</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr class="border-b dark:border-gray-700">
                                    <th class="py-2 pr-4">Row</th>
                                    <th class="py-2 pr-4">Data</th>
                                    <th class="py-2 pr-4">Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($invalidRows as $invalid)
                                    <tr class="border-b dark:border-gray-800">
                                        <td class="py-2 pr-4">{{ $invalid['row'] }}</td>
                                        <td class="py-2 pr-4">{{ $invalid['data']['name'] }} ({{ $invalid['data']['email'] }})</td>
                                        <td class="py-2 pr-4 text-danger-600">{{ implode('; ', $invalid['reasons']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @if (count($validRows) > 0)
                <div>
                    <h3 class="font-medium text-sm text-success-600 mb-2">Valid Rows (ready to import)</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr class="border-b dark:border-gray-700">
                                    <th class="py-2 pr-4">Name</th>
                                    <th class="py-2 pr-4">Email</th>
                                    <th class="py-2 pr-4">Phone</th>
                                    <th class="py-2 pr-4">Company</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($validRows as $valid)
                                    <tr class="border-b dark:border-gray-800">
                                        <td class="py-2 pr-4">{{ $valid['name'] }}</td>
                                        <td class="py-2 pr-4">{{ $valid['email'] }}</td>
                                        <td class="py-2 pr-4">{{ $valid['phone'] }}</td>
                                        <td class="py-2 pr-4">{{ $valid['company'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <x-filament::button wire:click="commit" color="success" class="mt-4" wire:loading.attr="disabled">
                        Import {{ count($validRows) }} Members
                    </x-filament::button>
                </div>
            @endif
        </x-filament::section>
    @endif
</x-filament-panels::page>
BLADE

php artisan optimize:clear

echo "Done: Excel import/export template extended with full Personal Data Form fields"
echo "(gender, race, DOB, IC no, addresses, employer, bank, etc. — all optional, 4 core fields still required)."
