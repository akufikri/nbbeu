<?php

namespace App\Filament\Resources\RenewalStatus\Tables;

use App\Models\AuditLog;
use App\Models\User;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RenewalStatusTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('renewal_expires_at')
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('member_no')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('renewal_expires_at')
                    ->date()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('renewal_status')
                    ->label('Renewal Status')
                    ->badge()
                    ->state(fn (User $record) => static::bucket($record)['label'])
                    ->color(fn (User $record) => static::bucket($record)['color']),
                TextColumn::make('last_reminder')
                    ->label('Last Reminder')
                    ->state(function (User $record) {
                        $log = AuditLog::where('subject_type', User::class)
                            ->where('subject_id', $record->id)
                            ->where('action', 'like', 'renewal.reminder_%')
                            ->latest('created_at')
                            ->first();

                        return $log ? "{$log->action} at {$log->created_at->format('Y-m-d')}" : '-';
                    }),
            ])
            ->filters([
                Filter::make('active')
                    ->label('Active')
                    ->query(fn (Builder $query) => $query->where('renewal_expires_at', '>', now()->addDays(30))),
                Filter::make('expiring_soon')
                    ->label('Expiring Soon')
                    ->query(fn (Builder $query) => $query->whereBetween('renewal_expires_at', [now(), now()->addDays(30)])),
                Filter::make('expired')
                    ->label('Expired')
                    ->query(fn (Builder $query) => $query->where('renewal_expires_at', '<', now())),
            ]);
    }

    /**
     * @return array{label: string, color: string}
     */
    public static function bucket(User $record): array
    {
        $expiresAt = $record->renewal_expires_at;

        if (! $expiresAt) {
            return ['label' => 'Expired', 'color' => 'danger'];
        }

        if ($expiresAt->greaterThan(now()->addDays(30))) {
            return ['label' => 'Active', 'color' => 'success'];
        }

        if ($expiresAt->greaterThan(now())) {
            return ['label' => 'Expiring Soon', 'color' => 'warning'];
        }

        return ['label' => 'Expired', 'color' => 'danger'];
    }
}
