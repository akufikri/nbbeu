<?php

namespace App\Filament\Widgets\Analytics;

use Filament\Widgets\ChartWidget;
use Livewire\Attributes\On;

class AgeChart extends ChartWidget
{
    use BuildsAnalyticsQuery;

    protected static bool $isDiscovered = false;

    protected int|string|array $columnSpan = 1;

    protected ?string $heading = 'Pecahan Kumpulan Umur';

    public array $analyticsFilters = [];

    #[On('analyticsFiltered')]
    public function filtersUpdated(array $filters): void
    {
        $this->analyticsFilters = $filters;
        $this->cachedData = null;
    }

    protected function getData(): array
    {
        $members = $this->getFilteredMembers($this->analyticsFilters);
        $groups  = ['<25' => 0, '25-35' => 0, '35-45' => 0, '45-55' => 0, '55+' => 0, 'Tidak Diketahui' => 0];

        foreach ($members as $user) {
            $dob = $user->memberProfile?->date_of_birth;
            if (! $dob) {
                $groups['Tidak Diketahui']++;
                continue;
            }
            $age = $dob->diffInYears(now());
            match (true) {
                $age < 25               => $groups['<25']++,
                $age >= 25 && $age < 35 => $groups['25-35']++,
                $age >= 35 && $age < 45 => $groups['35-45']++,
                $age >= 45 && $age < 55 => $groups['45-55']++,
                default                 => $groups['55+']++,
            };
        }

        return [
            'datasets' => [[
                'label'           => 'Ahli',
                'data'            => array_values($groups),
                'backgroundColor' => '#F59E0B',
            ]],
            'labels' => array_keys($groups),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
