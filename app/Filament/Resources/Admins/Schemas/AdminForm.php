<?php

namespace App\Filament\Resources\Admins\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AdminForm
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
                TextInput::make('password')
                    ->placeholder('Leave blank to keep unchanged')
                    ->password()
                    ->dehydrated(fn (?string $state) => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create'),
                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }
}
