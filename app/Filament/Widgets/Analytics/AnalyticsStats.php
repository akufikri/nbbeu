<?php

namespace App\Filament\Widgets\Analytics;

use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Livewire\Attributes\On;

class AnalyticsStats extends StatsOverviewWidget
{
    use BuildsAnalyticsQuery;

    protected static bool $isDiscovered = false;

    protected int|string|array $columnSpan = 'full';

    public array $analyticsFilters = [];

    #[On('analyticsFiltered')]
    public function filtersUpdated(array $filters): void
    {
        $this->analyticsFilters = $filters;
    }

    protected function getStats(): array
    {
        $members = $this->getFilteredMembers($this->analyticsFilters);
        $total   = $members->count();
        $male    = $members->filter(fn ($u) => $u->memberProfile?->gender === 'male')->count();
        $female  = $members->filter(fn ($u) => $u->memberProfile?->gender === 'female')->count();
        $active  = $members->where('member_status', 'active')->count();

        return [
            Stat::make('Jumlah Ahli', number_format($total))
                ->icon(Heroicon::OutlinedUsers)
                ->color('gray'),
            Stat::make('Lelaki', number_format($male))
                ->description($total > 0 ? round($male / $total * 100) . '%' : '—')
                ->icon(Heroicon::OutlinedUser)
                ->color('info'),
            Stat::make('Perempuan', number_format($female))
                ->description($total > 0 ? round($female / $total * 100) . '%' : '—')
                ->icon(Heroicon::OutlinedUser)
                ->color('danger'),
            Stat::make('Ahli Aktif', number_format($active))
                ->icon(Heroicon::OutlinedCheckBadge)
                ->color('success'),
        ];
    }
}
