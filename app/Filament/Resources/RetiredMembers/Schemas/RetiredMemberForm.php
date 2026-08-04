<?php

namespace App\Filament\Resources\RetiredMembers\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RetiredMemberForm
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
                TextInput::make('member_no')
                    ->disabled()
                    ->dehydrated(false),
                Select::make('member_status')
                    ->label('Status')
                    ->options([
                        'active' => 'Active',
                        'retired' => 'Retired',
                    ])
                    ->default('active')
                    ->required(),
                DatePicker::make('renewal_expires_at'),
            ]);
    }
}
