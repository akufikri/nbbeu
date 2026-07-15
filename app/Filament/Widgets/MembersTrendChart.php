<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;

class MembersTrendChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 1;

    protected ?string $heading = 'New Registrations (Last 12 Months)';

    protected function getData(): array
    {
        $months = collect(range(11, 0))->map(fn (int $i) => now()->subMonths($i)->startOfMonth());

        $counts = $months->map(
            fn ($month) => User::whereBetween('created_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])->count()
        );

        return [
            'datasets' => [
                [
                    'label' => 'New Registrations',
                    'data' => $counts->all(),
                    'borderColor' => '#18AFBF',
                    'backgroundColor' => 'rgba(24, 175, 191, 0.15)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $months->map(fn ($month) => $month->format('M Y'))->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
