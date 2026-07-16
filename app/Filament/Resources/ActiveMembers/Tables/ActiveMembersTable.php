<?php

namespace App\Filament\Resources\ActiveMembers\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ActiveMembersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('phone'),
                TextColumn::make('company')
                    ->searchable(),
                TextColumn::make('member_no')
                    ->placeholder('-'),
                TextColumn::make('approved_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('renewal_expires_at')
                    ->date()
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => $state && $state < now() ? 'danger' : 'gray'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ]);
    }
}
