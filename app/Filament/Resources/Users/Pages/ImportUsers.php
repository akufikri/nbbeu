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
