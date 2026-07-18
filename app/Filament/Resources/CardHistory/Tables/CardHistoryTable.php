<?php

namespace App\Filament\Resources\CardHistory\Tables;

use App\Actions\Membership\GenerateMemberCard;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class CardHistoryTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('issued_at', 'desc')
            ->columns([
                TextColumn::make('user.name')
                    ->searchable()
                    ->weight('medium'),
                TextColumn::make('user.member_no')
                    ->label('Member No.')
                    ->searchable(),
                TextColumn::make('card_number')
                    ->label('Card No.'),
                TextColumn::make('issued_at')
                    ->label('Card Issued')
                    ->date(),
                TextColumn::make('expires_at')
                    ->label('Card Expires')
                    ->date(),
            ])
            ->recordActions([
                Action::make('download_card')
                    ->label('Download Card')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(fn ($record) => response()->download(Storage::path($record->file_path), basename($record->file_path)))
                    ->visible(fn ($record) => filled($record->file_path) && Storage::exists($record->file_path)),
                Action::make('regenerate_card')
                    ->label('Regenerate Card')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        app(GenerateMemberCard::class)($record->user);

                        Notification::make()
                            ->success()
                            ->title('Member card regenerated')
                            ->send();
                    }),
            ]);
    }
}
