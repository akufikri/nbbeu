<?php

namespace App\Jobs;

use App\Models\MemberProfile;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportMembersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries   = 1;

    private const PROFILE_COLUMNS = [
        'gender', 'race', 'race_sub_group', 'date_of_birth', 'place_of_birth', 'ic_no',
        'postal_address', 'residential_address', 'occupation', 'position', 'employer_name',
        'employer_address', 'employment_date', 'bank_name', 'bank_branch', 'bank_address',
        'office_tel', 'office_fax', 'present_salary', 'salary_increment_date',
        'proposed_by_name', 'seconded_by_name',
    ];

    private const ALIASES = [
        'tel'               => 'phone',
        'department_branch' => 'company',
        'departmentbranch'  => 'company',
        'ic'                => 'ic_no',
    ];

    public function __construct(public readonly string $filePath) {}

    public function handle(): void
    {
        ini_set('memory_limit', '512M');

        Log::info('ImportMembersJob started', ['file' => $this->filePath]);

        if (! Storage::disk('local')->exists($this->filePath)) {
            Log::error('ImportMembersJob: file not found', ['file' => $this->filePath]);
            return;
        }

        try {
            $rows = $this->parseFile($this->filePath);
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($this->filePath);
            Log::error('ImportMembersJob: parse failed', ['error' => $e->getMessage()]);
            return;
        }

        Storage::disk('local')->delete($this->filePath);

        [$valid] = $this->validateRows($rows);

        if (empty($valid)) {
            Log::warning('ImportMembersJob: no valid rows');
            return;
        }

        $role     = \Spatie\Permission\Models\Role::where('name', 'member')->first();
        $roleId   = $role?->id;
        $password = bcrypt('password1234');
        $count    = 0;

        foreach (array_chunk($valid, 50) as $chunk) {
            DB::transaction(function () use ($chunk, $roleId, $password, &$count) {
                $now         = now();
                $userInserts = array_map(fn ($d) => [
                    'name'       => $d['name'],
                    'email'      => $d['email'],
                    'phone'      => $d['phone'],
                    'company'    => $d['company'],
                    'password'   => $password,
                    'status'     => 'approved',
                    'created_at' => $now,
                    'updated_at' => $now,
                ], $chunk);

                DB::table('users')->insert($userInserts);

                $newUsers = DB::table('users')
                    ->whereIn('email', array_column($userInserts, 'email'))
                    ->get(['id', 'email'])
                    ->keyBy('email');

                $roleRows = [];
                foreach ($chunk as $data) {
                    $uid = $newUsers->get($data['email'])?->id
                        ?? $newUsers->get(strtolower($data['email']))?->id;

                    if (! $uid) {
                        continue;
                    }

                    if ($roleId) {
                        $roleRows[] = [
                            'role_id'    => $roleId,
                            'model_type' => User::class,
                            'model_id'   => $uid,
                        ];
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
            });
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Log::info('ImportMembersJob done', ['imported' => $count, 'total_users' => User::count()]);
    }

    private function parseFile(string $filePath): array
    {
        $path    = Storage::disk('local')->path($filePath);
        $ext     = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $rawRows = $ext === 'csv' ? $this->readCsv($path) : $this->readXlsx($path);

        if (empty($rawRows)) {
            return [];
        }

        $headers = array_map(fn ($h) => Str::slug((string) $h, '_'), $rawRows[0]);

        $rows = [];
        foreach (array_slice($rawRows, 1) as $cells) {
            $row = [];
            foreach ($headers as $i => $header) {
                $row[$header] = trim((string) ($cells[$i] ?? ''));
            }
            $rows[] = $row;
        }

        $rows = array_values(array_filter($rows, fn ($r) => trim($r['name'] ?? '') !== '' || trim($r['email'] ?? '') !== ''));

        return array_map(function ($row) {
            foreach (self::ALIASES as $from => $to) {
                if (isset($row[$from]) && ! isset($row[$to])) {
                    $row[$to] = $row[$from];
                }
            }
            return $row;
        }, $rows);
    }

    private function validateRows(array $rows): array
    {
        $existingEmails = User::where('status', 'approved')
            ->pluck('email')
            ->map(fn ($e) => strtolower($e))
            ->flip()
            ->all();

        $seenEmails = [];
        $valid      = [];

        foreach ($rows as $index => $row) {
            $data = [
                'name'    => trim((string) ($row['name'] ?? '')),
                'email'   => trim((string) ($row['email'] ?? '')),
                'phone'   => trim((string) ($row['phone'] ?? '')),
                'company' => trim((string) ($row['company'] ?? '')),
            ];

            foreach (self::PROFILE_COLUMNS as $col) {
                $val        = trim((string) ($row[$col] ?? ''));
                $data[$col] = $val === '' ? null : $val;
            }

            if (empty($data['name']) || empty($data['email']) || ! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            $el = strtolower($data['email']);
            if (isset($seenEmails[$el]) || isset($existingEmails[$el])) {
                continue;
            }

            $seenEmails[$el] = true;
            $valid[]         = $data;
        }

        return [$valid, []];
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
                    $ss[]  = $cur;
                    $cur   = '';
                }
            }
            $reader->close();
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if ($sheetXml === false) {
            throw new \RuntimeException('No sheet1 found in xlsx');
        }

        $rawRows = [];
        $curRow  = null;
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
                    $col     = $m[1] ?? 'A';
                    $n       = 0;
                    foreach (str_split($col) as $c) {
                        $n = $n * 26 + (ord($c) - 64);
                    }
                    $curCell = $n - 1;
                    $curType = $reader->getAttribute('t');
                } elseif ($reader->localName === 'v' && $curCell !== null) {
                    $reader->read();
                    $val     = $reader->nodeType === \XMLReader::TEXT ? $reader->value : '';
                    if ($curType === 's') {
                        $val = $ss[(int) $val] ?? '';
                    }
                    $curRow[$curCell] = $val;
                    $curCell          = null;
                }
            } elseif ($reader->nodeType === \XMLReader::END_ELEMENT && $reader->localName === 'row' && $curRow !== null) {
                $rawRows[] = $curRow;
                $curRow    = null;
            }
        }
        $reader->close();

        return $rawRows;
    }

    private function readCsv(string $path): array
    {
        $handle  = fopen($path, 'r');
        $rawRows = [];
        while (($line = fgetcsv($handle)) !== false) {
            $rawRows[] = $line;
        }
        fclose($handle);

        return $rawRows;
    }
}
