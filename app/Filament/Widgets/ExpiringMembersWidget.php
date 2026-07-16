<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class ExpiringMembersWidget extends TableWidget
{
    protected static ?int $sort = 4;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Members Expiring Soon')
            ->query(
                User::query()
                    ->where('status', 'approved')
                    ->whereBetween('renewal_expires_at', [now(), now()->addDays(30)])
            )
            ->defaultSort('renewal_expires_at', 'asc')
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('member_no')
                    ->placeholder('-'),
                TextColumn::make('renewal_expires_at')
                    ->date()
                    ->sortable(),
                TextColumn::make('company'),
            ]);
    }
}
