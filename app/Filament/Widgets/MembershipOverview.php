<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use App\Models\User;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MembershipOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $revenue = Payment::where('status', 'paid')->sum('amount');

        return [
            Stat::make('Approved Members', User::where('status', 'approved')->count())
                ->icon(Heroicon::OutlinedCheckBadge)
                ->color('success'),
            Stat::make('Pending Review', User::where('status', 'pending')->count())
                ->icon(Heroicon::OutlinedClock)
                ->color('warning'),
            Stat::make('Rejected', User::where('status', 'rejected')->count())
                ->icon(Heroicon::OutlinedXCircle)
                ->color('danger'),
            Stat::make('Total Revenue', 'RM '.number_format($revenue, 2))
                ->icon(Heroicon::OutlinedBanknotes)
                ->color('gray'),
            Stat::make('Expired Renewal', User::where('status', 'approved')
                ->whereDate('renewal_expires_at', '<', now())
                ->count())
                ->icon(Heroicon::OutlinedExclamationTriangle)
                ->color('danger'),
            Stat::make('Renewal Due Soon', User::where('status', 'approved')
                ->whereDate('renewal_expires_at', '>=', now())
                ->whereDate('renewal_expires_at', '<=', now()->addDays(30))
                ->count())
                ->icon(Heroicon::OutlinedBellAlert)
                ->color('warning'),
        ];
    }
}
