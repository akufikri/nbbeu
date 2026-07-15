<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Imports\UsersImportReader;
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

            $validator = Validator::make($data, [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255'],
                'phone' => ['required', 'string', 'max:20'],
                'company' => ['required', 'string', 'max:255'],
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
            $user = User::create($data);
            $user->assignRole('member');
        }

        Notification::make()
            ->title(count($this->validRows).' members imported, '.count($this->invalidRows).' failed')
            ->success()
            ->send();

        $this->reset(['file', 'previewed', 'validRows', 'invalidRows']);
    }
}
