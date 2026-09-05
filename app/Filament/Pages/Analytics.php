<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\Analytics\AgeChart;
use App\Filament\Widgets\Analytics\AnalyticsStats;
use App\Filament\Widgets\Analytics\BankChart;
use App\Filament\Widgets\Analytics\GenderChart;
use App\Filament\Widgets\Analytics\RaceChart;
use App\Models\MemberProfile;
use App\Models\User;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class Analytics extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?string $navigationLabel = 'Analitik Ahli';

    protected static string|\UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.analytics';

    public array $data = [];

    public function mount(): void
    {
        $this->resetFilters();
    }

    public function resetFilters(): void
    {
        $this->data = [
            'gender'            => '',
            'race'              => '',
            'employer_name'     => '',
            'age_group'         => '',
            'salary_band'       => '',
            'member_status'     => '',
            'education_level'   => '',
            'employment_status' => '',
            'work_state'        => '',
            'position'          => '',
            'service_period'    => '',
        ];
        $this->form->fill($this->data);
        $this->dispatch('analyticsFiltered', filters: $this->data);
    }

    public function applyFilters(): void
    {
        $this->data = $this->form->getState();
        $this->dispatch('analyticsFiltered', filters: $this->data);
    }

    public function form(Schema $schema): Schema
    {
        $employers = MemberProfile::whereNotNull('employer_name')
            ->distinct()
            ->orderBy('employer_name')
            ->pluck('employer_name', 'employer_name')
            ->prepend('Semua Bank / Syarikat', '')
            ->toArray();

        $positions = MemberProfile::whereNotNull('position')
            ->distinct()
            ->orderBy('position')
            ->pluck('position', 'position')
            ->prepend('Semua Jawatan', '')
            ->toArray();

        return $schema
            ->components([
                Select::make('gender')
                    ->label('Jantina')
                    ->options(['' => 'Semua', 'male' => 'Lelaki', 'female' => 'Perempuan'])
                    ->default(''),

                Select::make('age_group')
                    ->label('Umur')
                    ->options([
                        ''      => 'Semua',
                        '<25'   => 'Bawah 25',
                        '25-35' => '25 – 35',
                        '35-45' => '35 – 45',
                        '45-55' => '45 – 55',
                        '55+'   => '55 ke atas',
                    ])
                    ->default(''),

                Select::make('education_level')
                    ->label('Tahap Pendidikan')
                    ->options([
                        ''        => 'Semua',
                        'spm'     => 'SPM / Setara',
                        'stpm'    => 'STPM',
                        'diploma' => 'Diploma',
                        'degree'  => 'Ijazah Sarjana Muda',
                        'masters' => 'Sarjana',
                        'phd'     => 'PhD',
                        'others'  => 'Lain-lain',
                    ])
                    ->default(''),

                Select::make('position')
                    ->label('Jawatan')
                    ->options($positions)
                    ->default('')
                    ->searchable(),

                Select::make('service_period')
                    ->label('Tempoh Perkhidmatan')
                    ->options([
                        ''      => 'Semua',
                        '<1'    => 'Bawah 1 tahun',
                        '1-5'   => '1 – 5 tahun',
                        '5-10'  => '5 – 10 tahun',
                        '10-20' => '10 – 20 tahun',
                        '20+'   => '20 tahun ke atas',
                    ])
                    ->default(''),

                Select::make('work_state')
                    ->label('Lokasi Tempat Bekerja')
                    ->options([
                        ''                => 'Semua',
                        'Johor'           => 'Johor',
                        'Kedah'           => 'Kedah',
                        'Kelantan'        => 'Kelantan',
                        'Melaka'          => 'Melaka',
                        'Negeri Sembilan' => 'Negeri Sembilan',
                        'Pahang'          => 'Pahang',
                        'Perak'           => 'Perak',
                        'Perlis'          => 'Perlis',
                        'Pulau Pinang'    => 'Pulau Pinang',
                        'Sabah'           => 'Sabah',
                        'Sarawak'         => 'Sarawak',
                        'Selangor'        => 'Selangor',
                        'Terengganu'      => 'Terengganu',
                        'WP Kuala Lumpur' => 'WP Kuala Lumpur',
                        'WP Labuan'       => 'WP Labuan',
                        'WP Putrajaya'    => 'WP Putrajaya',
                    ])
                    ->default(''),

                Select::make('employment_status')
                    ->label('Status Pekerjaan')
                    ->options([
                        ''          => 'Semua',
                        'permanent' => 'Tetap',
                        'contract'  => 'Kontrak',
                        'part_time' => 'Sambilan',
                        'others'    => 'Lain-lain',
                    ])
                    ->default(''),

                Select::make('salary_band')
                    ->label('Julat Gaji (RM)')
                    ->options([
                        ''       => 'Semua',
                        '<2000'  => '< RM 2,000',
                        '2-4k'   => 'RM 2,000 – 3,999',
                        '4-6k'   => 'RM 4,000 – 5,999',
                        '6-8k'   => 'RM 6,000 – 7,999',
                        '8k+'    => 'RM 8,000 ke atas',
                    ])
                    ->default(''),

                Select::make('employer_name')
                    ->label('Industri / Majikan')
                    ->options($employers)
                    ->default('')
                    ->searchable(),

                Select::make('race')
                    ->label('Kaum')
                    ->options(['' => 'Semua', 'malay' => 'Melayu', 'chinese' => 'Cina', 'indian' => 'India', 'bumiputra' => 'Bumiputra'])
                    ->default(''),

                Select::make('member_status')
                    ->label('Status Keahlian')
                    ->options(['' => 'Semua', 'active' => 'Aktif', 'retired' => 'Bersara', 'suspended' => 'Digantung'])
                    ->default(''),
            ])
            ->columns(3)
            ->statePath('data');
    }

    protected function getFooterWidgets(): array
    {
        return [
            AnalyticsStats::class,
            GenderChart::class,
            RaceChart::class,
            BankChart::class,
            AgeChart::class,
        ];
    }

    public function getFooterWidgetsColumns(): int|array
    {
        return 2;
    }

    public function getFilteredMembers(): Collection
    {
        $filters = $this->data;

        $query = User::query()
            ->where('status', 'approved')
            ->with('memberProfile')
            ->when($filters['member_status'] ?? '', fn (Builder $q, $v) => $q->where('member_status', $v));

        $profileFilters = array_filter([
            'gender'            => $filters['gender'] ?? '',
            'race'              => $filters['race'] ?? '',
            'employer_name'     => $filters['employer_name'] ?? '',
            'education_level'   => $filters['education_level'] ?? '',
            'employment_status' => $filters['employment_status'] ?? '',
            'work_state'        => $filters['work_state'] ?? '',
            'position'          => $filters['position'] ?? '',
        ]);

        if ($profileFilters) {
            $query->whereHas('memberProfile', function (Builder $q) use ($profileFilters) {
                foreach ($profileFilters as $col => $val) {
                    $q->where($col, $val);
                }
            });
        }

        if ($ageGroup = ($filters['age_group'] ?? '')) {
            $now = now();
            $query->whereHas('memberProfile', function (Builder $q) use ($ageGroup, $now) {
                match ($ageGroup) {
                    '<25'   => $q->where('date_of_birth', '>', $now->copy()->subYears(25)),
                    '25-35' => $q->whereBetween('date_of_birth', [$now->copy()->subYears(36), $now->copy()->subYears(25)]),
                    '35-45' => $q->whereBetween('date_of_birth', [$now->copy()->subYears(46), $now->copy()->subYears(35)]),
                    '45-55' => $q->whereBetween('date_of_birth', [$now->copy()->subYears(56), $now->copy()->subYears(45)]),
                    '55+'   => $q->where('date_of_birth', '<', $now->copy()->subYears(55)),
                    default => null,
                };
            });
        }

        if ($servicePeriod = ($filters['service_period'] ?? '')) {
            $now = now();
            $query->whereHas('memberProfile', function (Builder $q) use ($servicePeriod, $now) {
                match ($servicePeriod) {
                    '<1'    => $q->where('employment_date', '>', $now->copy()->subYear()),
                    '1-5'   => $q->whereBetween('employment_date', [$now->copy()->subYears(5), $now->copy()->subYear()]),
                    '5-10'  => $q->whereBetween('employment_date', [$now->copy()->subYears(10), $now->copy()->subYears(5)]),
                    '10-20' => $q->whereBetween('employment_date', [$now->copy()->subYears(20), $now->copy()->subYears(10)]),
                    '20+'   => $q->where('employment_date', '<', $now->copy()->subYears(20)),
                    default => null,
                };
            });
        }

        $members = $query->get();

        if ($salaryBand = ($filters['salary_band'] ?? '')) {
            $members = $members->filter(function ($user) use ($salaryBand) {
                $raw = (float) preg_replace('/[^\d.]/', '', (string) ($user->memberProfile?->present_salary ?? ''));
                if ($raw === 0.0) {
                    return false;
                }

                return match ($salaryBand) {
                    '<2000' => $raw < 2000,
                    '2-4k'  => $raw >= 2000 && $raw < 4000,
                    '4-6k'  => $raw >= 4000 && $raw < 6000,
                    '6-8k'  => $raw >= 6000 && $raw < 8000,
                    '8k+'   => $raw >= 8000,
                    default => true,
                };
            });
        }

        return $members->values();
    }
}
