<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->placeholder('Full name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->placeholder('name@email.com')
                    ->email()
                    ->required(),
                TextInput::make('phone')
                    ->placeholder('+60 12-345 6789')
                    ->tel()
                    ->required(),
                TextInput::make('company')
                    ->placeholder('Company / bank name')
                    ->required(),
                Select::make('roles')
                    ->placeholder('Select role')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),
                DateTimePicker::make('email_verified_at'),
                TextInput::make('password')
                    ->placeholder('Leave blank to keep unchanged')
                    ->password()
                    ->dehydrated(fn (?string $state) => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create'),
                Select::make('status')
                    ->options(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'])
                    ->default('pending')
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText('Change status via the Approve/Reject action in the table, not here.')
                    ->required(),
                TextInput::make('member_no')
                    ->disabled()
                    ->dehydrated(false),
                Textarea::make('rejection_reason')
                    ->disabled()
                    ->dehydrated(false)
                    ->columnSpanFull(),
                DateTimePicker::make('approved_at')
                    ->disabled()
                    ->dehydrated(false),
                DatePicker::make('renewal_expires_at'),
            ]);
    }
}
