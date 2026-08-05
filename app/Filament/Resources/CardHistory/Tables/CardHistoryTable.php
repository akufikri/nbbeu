<?php

namespace App\Filament\Resources\CardHistory\Tables;

use App\Actions\Membership\GenerateMemberCard;
use App\Actions\Membership\RenderMemberCardImage;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use ZipArchive;

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
                Action::make('preview_card')
                    ->label('Preview')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Card Preview')
                    ->modalContent(fn ($record) => view('filament.modals.image-preview', [
                        'url' => route('admin.documents.cards.preview', $record),
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
                // Same PNG renderer + zip format the member portal download uses,
                // so admin and member downloads are always identical.
                Action::make('download_card')
                    ->label('Download Card')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function ($record) {
                        $frontPng = app(RenderMemberCardImage::class)($record->user, $record);
                        $backPng = file_get_contents(public_path('assets/illustrations/back-kad-ahli.png'));

                        $tmpPath = tempnam(sys_get_temp_dir(), 'nbbeu-card-').'.zip';

                        $zip = new ZipArchive();
                        $zip->open($tmpPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
                        $zip->addFromString('Member-Card-Front.png', $frontPng);
                        $zip->addFromString('Member-Card-Back.png', $backPng);
                        $zip->close();

                        return response()->download($tmpPath, "Member-Card-{$record->user->member_no}.zip")->deleteFileAfterSend();
                    }),
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
