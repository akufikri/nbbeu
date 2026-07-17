<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\AuditLog;
use App\Models\MemberProfile;
use Filament\Actions\Action;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

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
                Section::make('Member Profile')
                    ->visible(fn ($record) => $record->memberProfile !== null)
                    ->columns(2)
                    ->components([
                        TextEntry::make('memberProfile.gender')->label('Gender')->placeholder('-'),
                        TextEntry::make('memberProfile.race')
                            ->label('Race')
                            ->placeholder('-')
                            ->formatStateUsing(fn (?string $state, $record) => $state === 'bumiputra' && filled($record->memberProfile->race_sub_group)
                                ? "{$state} ({$record->memberProfile->race_sub_group})"
                                : $state),
                        TextEntry::make('memberProfile.date_of_birth')->label('Date of birth')->date()->placeholder('-'),
                        TextEntry::make('memberProfile.occupation')->label('Occupation')->placeholder('-'),
                        TextEntry::make('memberProfile.position')->label('Position')->placeholder('-'),
                        TextEntry::make('memberProfile.employer_name')->label('Employer')->placeholder('-'),
                        TextEntry::make('memberProfile.bank_name')->label('Bank')->placeholder('-'),
                        TextEntry::make('ic_no_masked')
                            ->label('IC No.')
                            ->state(fn ($record) => $record->memberProfile->maskedIcNo())
                            ->suffixAction(self::revealAction('ic_no', 'IC number')),
                        TextEntry::make('present_salary_masked')
                            ->label('Present Salary')
                            ->state(fn ($record) => $record->memberProfile->maskedSalary())
                            ->suffixAction(self::revealAction('present_salary', 'present salary')),
                    ]),
                Section::make('Sponsors')
                    ->visible(fn ($record) => $record->memberProfile !== null)
                    ->columns(2)
                    ->components([
                        TextEntry::make('memberProfile.proposedBy.name')
                            ->label('Proposed By')
                            ->placeholder('-')
                            ->formatStateUsing(fn (?string $state, $record) => filled($state) && filled($record->memberProfile->proposedBy?->member_no)
                                ? "{$state} ({$record->memberProfile->proposedBy->member_no})"
                                : $state),
                        TextEntry::make('memberProfile.secondedBy.name')
                            ->label('Seconded By')
                            ->placeholder('-')
                            ->formatStateUsing(fn (?string $state, $record) => filled($state) && filled($record->memberProfile->secondedBy?->member_no)
                                ? "{$state} ({$record->memberProfile->secondedBy->member_no})"
                                : $state),
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

    /**
     * PDPA-sensitive field reveal action (TRD §4.1): every reveal is audit-logged.
     */
    protected static function revealAction(string $field, string $fieldLabel): Action
    {
        return Action::make("reveal_{$field}")
            ->label('Reveal')
            ->icon(Heroicon::OutlinedEye)
            ->color('gray')
            ->requiresConfirmation()
            ->modalHeading("Reveal {$fieldLabel}")
            ->modalDescription("This will log an audit entry recording that you viewed this member's {$fieldLabel}.")
            ->action(function ($record) use ($field) {
                /** @var MemberProfile $profile */
                $profile = $record->memberProfile;

                AuditLog::create([
                    'user_id' => Auth::id(),
                    'action' => 'member_profile.sensitive_data_revealed',
                    'subject_type' => MemberProfile::class,
                    'subject_id' => $profile->id,
                    'meta' => ['field' => $field, 'revealed_by' => Auth::id()],
                ]);

                Notification::make()
                    ->title(ucfirst(str_replace('_', ' ', $field)).': '.$profile->{$field})
                    ->success()
                    ->send();
            });
    }
}
