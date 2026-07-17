<?php

namespace App\Filament\Resources\UnionDues\Tables;

use App\Models\UnionDuesMandate;
use App\Models\UnionDuesRecord;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;

class UnionDuesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Name')
                    ->searchable(),
                TextColumn::make('user.member_no')
                    ->label('Member No.')
                    ->placeholder('-'),
                TextColumn::make('deduction_amount')
                    ->label('Deduction')
                    ->money('MYR'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'cancelled' => 'gray',
                        default => 'amber',
                    }),
                TextColumn::make('consent_signed_at')
                    ->label('Consent Signed')
                    ->dateTime(),
                IconColumn::make('this_month_recorded')
                    ->label('This Month Recorded?')
                    ->state(function (UnionDuesMandate $record) {
                        if ($record->status !== 'active') {
                            return null;
                        }

                        return $record->records()
                            ->where('period_month', now()->format('Y-m'))
                            ->exists();
                    })
                    ->boolean()
                    ->placeholder('-'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->placeholder('All statuses')
                    ->options([
                        'pending_submission' => 'Pending Submission',
                        'active' => 'Active',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->recordActions([
                Action::make('recordPayment')
                    ->label('Record Payment')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn (UnionDuesMandate $record) => $record->status === 'active')
                    ->schema([
                        TextInput::make('period_month')
                            ->label('Period (YYYY-MM)')
                            ->default(now()->format('Y-m'))
                            ->placeholder('YYYY-MM')
                            ->regex('/^\d{4}-(0[1-9]|1[0-2])$/')
                            ->required(),
                        TextInput::make('amount_received')
                            ->label('Amount Received')
                            ->numeric()
                            ->prefix('RM')
                            ->required(),
                    ])
                    ->action(function (UnionDuesMandate $record, array $data): void {
                        try {
                            UnionDuesRecord::create([
                                'mandate_id' => $record->id,
                                'period_month' => $data['period_month'],
                                'amount_received' => $data['amount_received'],
                                'recorded_by' => Auth::id(),
                                'recorded_at' => now(),
                            ]);
                        } catch (QueryException $e) {
                            if ($e->getCode() === '23000') {
                                Notification::make()
                                    ->title('Already recorded for this period')
                                    ->danger()
                                    ->send();

                                return;
                            }

                            throw $e;
                        }

                        Notification::make()
                            ->title('Payment recorded')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
