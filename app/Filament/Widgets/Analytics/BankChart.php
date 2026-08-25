<?php

namespace App\Filament\Widgets\Analytics;

use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Livewire\Attributes\On;

class BankChart extends ChartWidget
{
    use BuildsAnalyticsQuery;

    protected static bool $isDiscovered = false;

    protected int|string|array $columnSpan = 1;

    protected ?string $heading = 'Ahli Mengikut Bank / Syarikat (Top 10)';

    protected ?string $maxHeight = '300px';

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
        $byBank  = $members
            ->groupBy(fn ($u) => $u->memberProfile?->employer_name ?? ($u->company ?? 'Tidak Dinyatakan'))
            ->map->count()
            ->sortDesc()
            ->take(10);

        return [
            'datasets' => [[
                'label'           => 'Ahli',
                'data'            => $byBank->values()->all(),
                'backgroundColor' => '#6366F1',
            ]],
            'labels' => $byBank->keys()->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array|RawJs|null
    {
        return [
            'indexAxis' => 'y',
            'plugins'   => ['legend' => ['display' => false]],
            'scales'    => ['x' => ['beginAtZero' => true, 'ticks' => ['stepSize' => 1]]],
        ];
    }
}
