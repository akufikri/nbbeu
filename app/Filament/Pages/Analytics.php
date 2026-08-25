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
            'gender'        => '',
            'race'          => '',
            'employer_name' => '',
            'age_group'     => '',
            'salary_band'   => '',
            'member_status' => '',
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

        return $schema
            ->components([
                Select::make('gender')
                    ->label('Jantina')
                    ->options(['' => 'Semua', 'male' => 'Lelaki', 'female' => 'Perempuan'])
                    ->default(''),

                Select::make('race')
                    ->label('Kaum')
                    ->options(['' => 'Semua', 'malay' => 'Melayu', 'chinese' => 'Cina', 'indian' => 'India', 'bumiputra' => 'Bumiputra'])
                    ->default(''),

                Select::make('employer_name')
                    ->label('Bank / Syarikat')
                    ->options($employers)
                    ->default(''),

                Select::make('age_group')
                    ->label('Kumpulan Umur')
                    ->options([
                        ''      => 'Semua',
                        '<25'   => 'Bawah 25',
                        '25-35' => '25 – 35',
                        '35-45' => '35 – 45',
                        '45-55' => '45 – 55',
                        '55+'   => '55 ke atas',
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

        $hasProfileFilter = ($filters['gender'] ?? '') || ($filters['race'] ?? '') || ($filters['employer_name'] ?? '');
        if ($hasProfileFilter) {
            $query->whereHas('memberProfile', function (Builder $q) use ($filters) {
                if ($filters['gender'] ?? '') {
                    $q->where('gender', $filters['gender']);
                }
                if ($filters['race'] ?? '') {
                    $q->where('race', $filters['race']);
                }
                if ($filters['employer_name'] ?? '') {
                    $q->where('employer_name', $filters['employer_name']);
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
