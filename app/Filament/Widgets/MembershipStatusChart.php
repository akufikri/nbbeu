<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;

class MembershipStatusChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 1;

    protected ?string $heading = 'Membership Status Breakdown';

    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'data' => [
                        User::where('status', 'approved')->count(),
                        User::where('status', 'pending')->count(),
                        User::where('status', 'rejected')->count(),
                    ],
                    'backgroundColor' => ['#16A34A', '#D97706', '#DC2626'],
                ],
            ],
            'labels' => ['Approved', 'Pending', 'Rejected'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
