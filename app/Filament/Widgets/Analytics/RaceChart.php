<?php

namespace App\Filament\Widgets\Analytics;

use Filament\Widgets\ChartWidget;
use Livewire\Attributes\On;

class RaceChart extends ChartWidget
{
    use BuildsAnalyticsQuery;

    protected static bool $isDiscovered = false;

    protected int|string|array $columnSpan = 1;

    protected ?string $heading = 'Pecahan Kaum';

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
        $byRace  = $members->groupBy(fn ($u) => $u->memberProfile?->race ?? 'unknown')->map->count()->sortDesc();

        $colors = ['#3B82F6', '#F59E0B', '#10B981', '#8B5CF6', '#EF4444', '#06B6D4', '#84CC16', '#EC4899'];

        return [
            'datasets' => [[
                'label'           => 'Ahli',
                'data'            => $byRace->values()->all(),
                'backgroundColor' => array_slice($colors, 0, $byRace->count()),
            ]],
            'labels' => $byRace->keys()->map(fn ($k) => ucfirst($k))->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
