<?php

namespace App\Console\Commands;

use App\Models\MemberProfile;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ImportMemberDetail extends Command
{
    protected $signature = 'nbbeu:import {file : Path to xlsx/csv file} {--dry-run : Preview only, do not commit}';

    protected $description = 'Import members from Excel file directly (bypasses Livewire)';

    private const PROFILE_COLUMNS = [
        'gender', 'race', 'race_sub_group', 'date_of_birth', 'place_of_birth', 'ic_no',
        'postal_address', 'residential_address', 'occupation', 'position', 'employer_name',
        'employer_address', 'employment_date', 'bank_name', 'bank_branch', 'bank_address',
        'office_tel', 'office_fax', 'present_salary', 'salary_increment_date',
        'proposed_by_name', 'seconded_by_name',
    ];

    public function handle(): int
    {
        $filePath = $this->argument('file');
        $dryRun = $this->option('dry-run');

        if (! file_exists($filePath)) {
            $this->error("File not found: $filePath");
            return 1;
        }

        $this->info("Reading file: $filePath");

        $rows = $this->readFile($filePath);
        $this->info("Raw rows (excl header): " . count($rows));

        $rows = array_values(array_filter($rows, fn ($r) => trim($r['name'] ?? '') !== '' || trim($r['email'] ?? '') !== ''));
        $this->info("Non-empty rows: " . count($rows));

        $aliases = ['tel' => 'phone', 'department_branch' => 'company', 'departmentbranch' => 'company', 'ic' => 'ic_no'];
        $rows = array_map(function ($row) use ($aliases) {
            foreach ($aliases as $from => $to) {
                if (isset($row[$from]) && ! isset($row[$to])) {
                    $row[$to] = $row[$from];
                }
            }
            return $row;
        }, $rows);

        $seenEmails = [];
        $existingEmails = User::where('status', 'approved')->pluck('email')->map(fn ($e) => strtolower($e))->flip()->all();
        $valid = [];
        $invalid = [];

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;
            $data = [
                'name'    => trim((string) ($row['name'] ?? '')),
                'email'   => trim((string) ($row['email'] ?? '')),
                'phone'   => trim((string) ($row['phone'] ?? '')),
                'company' => trim((string) ($row['company'] ?? '')),
            ];

            foreach (self::PROFILE_COLUMNS as $col) {
                $val = trim((string) ($row[$col] ?? ''));
                $data[$col] = $val === '' ? null : $val;
            }

            $v = Validator::make($data, [
                'name'    => ['required', 'string', 'max:255'],
                'email'   => ['required', 'email', 'max:255'],
                'phone'   => ['required', 'string', 'max:20'],
                'company' => ['required', 'string', 'max:255'],
                'gender'  => ['nullable', 'in:' . implode(',', array_keys(MemberProfile::GENDERS))],
                'race'    => ['nullable', 'in:' . implode(',', array_keys(MemberProfile::RACES))],
                'ic_no'   => ['nullable', 'string', 'max:30'],
            ]);

            $reasons = $v->fails() ? array_values($v->errors()->all()) : [];

            if (! $reasons) {
                $el = strtolower($data['email']);
                if (isset($seenEmails[$el])) {
                    $reasons[] = "Duplicate (row {$seenEmails[$el]})";
                } elseif (isset($existingEmails[$el])) {
                    $reasons[] = 'Already approved member';
                } else {
                    $seenEmails[$el] = $rowNumber;
                }
            }

            if ($reasons) {
                $invalid[] = ['row' => $rowNumber, 'reasons' => $reasons, 'data' => $data];
            } else {
                $valid[] = $data;
            }
        }

        $this->info("Valid: " . count($valid) . ", Invalid: " . count($invalid));

        if ($invalid) {
            $this->warn("Invalid rows:");
            foreach (array_slice($invalid, 0, 10) as $inv) {
                $this->line("  Row {$inv['row']}: {$inv['data']['name']} ({$inv['data']['email']}) — " . implode('; ', $inv['reasons']));
            }
            if (count($invalid) > 10) {
                $this->line("  ... and " . (count($invalid) - 10) . " more");
            }
        }

        if (empty($valid)) {
            $this->error("No valid rows. Aborting.");
            return 1;
        }

        if ($dryRun) {
            $this->info("DRY RUN — no changes made.");
            return 0;
        }

        if (! $this->confirm("Import " . count($valid) . " members?")) {
            $this->info("Aborted.");
            return 0;
        }

        $role = \Spatie\Permission\Models\Role::where('name', 'member')->first();
        $roleId = $role?->id;
        $count = 0;

        $chunks = array_chunk($valid, 50);
        $bar = $this->output->createProgressBar(count($valid));
        $bar->start();

        foreach ($chunks as $chunk) {
            try {
                DB::transaction(function () use ($chunk, $roleId, &$count, $bar) {
                    $userInserts = [];
                    $now = now();
                    foreach ($chunk as $data) {
                        $userInserts[] = [
                            'name'       => $data['name'],
                            'email'      => $data['email'],
                            'phone'      => $data['phone'],
                            'company'    => $data['company'],
                            'password'   => null,
                            'status'     => 'approved',
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }

                    DB::table('users')->insert($userInserts);

                    $emails = array_column($userInserts, 'email');
                    $newUsers = DB::table('users')->whereIn('email', $emails)->get(['id', 'email'])->keyBy('email');

                    $roleRows = [];
                    foreach ($chunk as $data) {
                        $uid = $newUsers->get($data['email'])?->id
                            ?? $newUsers->get(strtolower($data['email']))?->id;

                        if (! $uid) {
                            continue;
                        }

                        if ($roleId) {
                            $roleRows[] = ['role_id' => $roleId, 'model_type' => User::class, 'model_id' => $uid];
                        }

                        $profileData = array_filter(
                            array_intersect_key($data, array_flip(self::PROFILE_COLUMNS)),
                            fn ($v) => $v !== null
                        );

                        if ($profileData) {
                            User::find($uid)?->memberProfile()->create($profileData);
                        }
                    }

                    if ($roleRows) {
                        DB::table('model_has_roles')->insert($roleRows);
                    }

                    $count += count($chunk);
                    $bar->advance(count($chunk));
                });
            } catch (\Throwable $e) {
                $bar->finish();
                $this->newLine();
                $this->error("Chunk failed: " . $e->getMessage());
                $this->line($e->getFile() . ':' . $e->getLine());
                return 1;
            }
        }

        $bar->finish();
        $this->newLine();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->info("Done. Imported: $count, Skipped: " . count($invalid));
        $this->info("Total users now: " . User::count());

        return 0;
    }

    private function readFile(string $path): array
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return $ext === 'csv' ? $this->readCsv($path) : $this->readXlsx($path);
    }

    private function readXlsx(string $path): array
    {
        $zip = new \ZipArchive;
        if ($zip->open($path) !== true) {
            throw new \RuntimeException('Cannot open xlsx file');
        }

        $ss = [];
        if (($ssXml = $zip->getFromName('xl/sharedStrings.xml')) !== false) {
            $reader = new \XMLReader;
            $reader->XML($ssXml);
            $cur = '';
            while ($reader->read()) {
                if ($reader->nodeType === \XMLReader::ELEMENT && $reader->localName === 't') {
                    $reader->read();
                    $cur .= $reader->nodeType === \XMLReader::TEXT ? $reader->value : '';
                } elseif ($reader->nodeType === \XMLReader::END_ELEMENT && $reader->localName === 'si') {
                    $ss[] = $cur;
                    $cur = '';
                }
            }
            $reader->close();
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        $rawRows = [];
        $curRow = null;
        $curCell = null;
        $curType = null;

        $reader = new \XMLReader;
        $reader->XML($sheetXml);
        while ($reader->read()) {
            if ($reader->nodeType === \XMLReader::ELEMENT) {
                if ($reader->localName === 'row') {
                    $curRow = [];
                } elseif ($reader->localName === 'c' && $curRow !== null) {
                    preg_match('/([A-Z]+)/', (string) $reader->getAttribute('r'), $m);
                    $col = $m[1] ?? 'A';
                    $n = 0;
                    foreach (str_split($col) as $c) {
                        $n = $n * 26 + (ord($c) - 64);
                    }
                    $curCell = $n - 1;
                    $curType = $reader->getAttribute('t');
                } elseif ($reader->localName === 'v' && $curCell !== null) {
                    $reader->read();
                    $val = $reader->nodeType === \XMLReader::TEXT ? $reader->value : '';
                    if ($curType === 's') {
                        $val = $ss[(int) $val] ?? '';
                    }
                    $curRow[$curCell] = $val;
                    $curCell = null;
                }
            } elseif ($reader->nodeType === \XMLReader::END_ELEMENT && $reader->localName === 'row' && $curRow !== null) {
                $rawRows[] = $curRow;
                $curRow = null;
            }
        }
        $reader->close();

        if (empty($rawRows)) {
            return [];
        }

        $headers = array_map(fn ($h) => Str::slug((string) $h, '_'), $rawRows[0]);
        $rows = [];
        foreach (array_slice($rawRows, 1) as $cells) {
            $row = [];
            foreach ($headers as $i => $header) {
                $row[$header] = trim($cells[$i] ?? '');
            }
            $rows[] = $row;
        }

        return $rows;
    }

    private function readCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        $headers = null;
        $rows = [];
        while (($line = fgetcsv($handle)) !== false) {
            if ($headers === null) {
                $headers = array_map(fn ($h) => Str::slug((string) $h, '_'), $line);
                continue;
            }
            $row = [];
            foreach ($headers as $i => $header) {
                $row[$header] = trim($line[$i] ?? '');
            }
            $rows[] = $row;
        }
        fclose($handle);

        return $rows;
    }
}
