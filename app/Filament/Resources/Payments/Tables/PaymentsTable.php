<?php

namespace App\Filament\Resources\Payments\Tables;

use App\Events\PaymentConfirmed;
use App\Models\Payment;
use App\Services\ToyyibpayService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('user.name')
                    ->searchable(),
                TextColumn::make('user.email')
                    ->searchable(),
                TextColumn::make('amount')
                    ->money('MYR'),
                TextColumn::make('purpose'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'failed' => 'danger',
                        default => 'amber',
                    }),
                TextColumn::make('toyyibpay_bill_code')
                    ->placeholder('-'),
                TextColumn::make('toyyibpay_ref_no')
                    ->placeholder('-'),
                TextColumn::make('paid_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->placeholder('All statuses')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                    ]),
            ])
            ->recordActions([
                Action::make('checkStatus')
                    ->label('Check Status')
                    ->icon('heroicon-o-arrow-path')
                    ->visible(fn (Payment $record): bool => $record->status !== 'paid' && filled($record->toyyibpay_bill_code))
                    ->action(function (Payment $record, ToyyibpayService $toyyibpay): void {
                        if ($toyyibpay->isBillPaid($record->toyyibpay_bill_code)) {
                            $record->update([
                                'status' => 'paid',
                                'paid_at' => now(),
                            ]);

                            event(new PaymentConfirmed($record));

                            Notification::make()
                                ->title('Status updated')
                                ->success()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title('Still pending')
                            ->body('Toyyibpay has not confirmed payment for this bill yet.')
                            ->warning()
                            ->send();
                    }),
            ]);
    }
}
