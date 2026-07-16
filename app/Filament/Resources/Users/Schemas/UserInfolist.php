<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Personal data')
                    ->columns(2)
                    ->components([
                        TextEntry::make('name'),
                        TextEntry::make('email')->label('Email address'),
                        TextEntry::make('phone'),
                        TextEntry::make('company'),
                        TextEntry::make('member_no')->placeholder('-'),
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'approved' => 'success',
                                'rejected' => 'danger',
                                default => 'warning',
                            }),
                        TextEntry::make('approved_at')->dateTime(),
                        TextEntry::make('approvedBy.name')->label('Approved by')->placeholder('-'),
                        TextEntry::make('rejection_reason')
                            ->label('Rejection reason')
                            ->columnSpanFull()
                            ->visible(fn ($record) => $record->status === 'rejected'),
                    ]),
                Section::make('Payments')
                    ->components([
                        RepeatableEntry::make('payments')
                            ->hiddenLabel()
                            ->schema([
                                TextEntry::make('amount')->money('MYR'),
                                TextEntry::make('status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'paid' => 'success',
                                        'failed' => 'danger',
                                        default => 'warning',
                                    }),
                                TextEntry::make('paid_at')->dateTime()->placeholder('-'),
                                TextEntry::make('toyyibpay_bill_code')->label('Bill code')->placeholder('-'),
                            ])
                            ->columns(4),
                    ]),
            ]);
    }
}
