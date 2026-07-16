<?php

namespace App\Filament\Resources\Users\Tables;

use App\Events\UserApproved;
use App\Events\UserRejected;
use App\Exports\UsersExport;
use App\Filament\Resources\Users\Pages\ImportUsers;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                Action::make('export')
                    ->label('Export Excel')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->color('gray')
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->placeholder('All statuses')
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ]),
                        DatePicker::make('registered_from')->label('Registered from')->placeholder('Select date'),
                        DatePicker::make('registered_until')->label('Registered until')->placeholder('Select date'),
                        Select::make('renewal_status')
                            ->label('Renewal Status')
                            ->placeholder('All')
                            ->options(['active' => 'Still Active', 'expired' => 'Expired']),
                    ])
                    ->action(function (array $data) {
                        return Excel::download(
                            new UsersExport(
                                status: $data['status'] ?? null,
                                registeredFrom: $data['registered_from'] ?? null,
                                registeredUntil: $data['registered_until'] ?? null,
                                renewalStatus: $data['renewal_status'] ?? null,
                            ),
                            'nbbeu-members-'.now()->format('Y-m-d').'.xlsx'
                        );
                    }),
                Action::make('import')
                    ->label('Import Excel')
                    ->icon(Heroicon::OutlinedArrowUpTray)
                    ->color('gray')
                    ->url(fn () => ImportUsers::getUrl()),
            ])
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('company')
                    ->searchable(),
                TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge(),
                TextColumn::make('latest_payment_status')
                    ->label('Payment')
                    ->badge()
                    ->state(fn ($record) => $record->payments()->latest('id')->value('status') ?? '-')
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'failed' => 'danger',
                        'pending' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'warning',
                    }),
                TextColumn::make('member_no')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('approved_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('renewal_expires_at')
                    ->date()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->placeholder('All statuses')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Approve')
                    ->icon(Heroicon::OutlinedCheck)
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->forceFill([
                            'status' => 'approved',
                            'approved_at' => now(),
                            'approved_by' => Auth::id(),
                        ])->save();

                        event(new UserApproved($record, Auth::user()));

                        Notification::make()
                            ->title('Member approved, documents are being processed')
                            ->success()
                            ->send();
                    }),
                Action::make('reject')
                    ->label('Reject')
                    ->icon(Heroicon::OutlinedXMark)
                    ->color('danger')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->schema([
                        Textarea::make('rejection_reason')
                            ->label('Rejection reason')
                            ->placeholder('Explain why this application is being rejected')
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $record->forceFill([
                            'status' => 'rejected',
                            'rejection_reason' => $data['rejection_reason'],
                        ])->save();

                        event(new UserRejected($record, Auth::user()));

                        Notification::make()
                            ->title('Member rejected')
                            ->danger()
                            ->send();
                    }),
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
