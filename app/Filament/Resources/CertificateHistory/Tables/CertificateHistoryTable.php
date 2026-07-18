<?php

namespace App\Filament\Resources\CertificateHistory\Tables;

use App\Actions\Membership\GenerateCertificate;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class CertificateHistoryTable
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
                TextColumn::make('cert_number')
                    ->label('Certificate No.'),
                TextColumn::make('issued_at')
                    ->label('Certificate Issued')
                    ->date(),
            ])
            ->recordActions([
                Action::make('download_certificate')
                    ->label('Download Certificate')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(fn ($record) => response()->download(Storage::path($record->file_path), basename($record->file_path)))
                    ->visible(fn ($record) => filled($record->file_path) && Storage::exists($record->file_path)),
                Action::make('regenerate_certificate')
                    ->label('Regenerate Certificate')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        app(GenerateCertificate::class)($record->user);

                        Notification::make()
                            ->success()
                            ->title('Certificate regenerated')
                            ->send();
                    }),
            ]);
    }
}
