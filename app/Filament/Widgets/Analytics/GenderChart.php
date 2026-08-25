<?php

namespace App\Filament\Widgets\Analytics;

use Filament\Widgets\ChartWidget;
use Livewire\Attributes\On;

class GenderChart extends ChartWidget
{
    use BuildsAnalyticsQuery;

    protected static bool $isDiscovered = false;

    protected int|string|array $columnSpan = 1;

    protected ?string $heading = 'Pecahan Jantina';

    public array $analyticsFilters = [];

    #[On('analyticsFiltered')]
    public function filtersUpdated(array $filters): void
    {
        $this->analyticsFilters = $filters;
        $this->cachedData = null;
    }

    protected function getData(): array
    {
        $members  = $this->getFilteredMembers($this->analyticsFilters);
        $byGender = $members->groupBy(fn ($u) => $u->memberProfile?->gender ?? 'unknown')->map->count();

        return [
            'datasets' => [[
                'data'            => $byGender->values()->all(),
                'backgroundColor' => ['#3B82F6', '#EC4899', '#9CA3AF'],
            ]],
            'labels' => $byGender->keys()->map(fn ($k) => match ($k) {
                'male'   => 'Lelaki',
                'female' => 'Perempuan',
                default  => ucfirst($k),
            })->all(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
