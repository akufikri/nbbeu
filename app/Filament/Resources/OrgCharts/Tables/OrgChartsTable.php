<?php

namespace App\Filament\Resources\OrgCharts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrgChartsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('display_order')
            ->reorderable('display_order')
            ->columns([
                ImageColumn::make('photo')
                    ->disk('public')
                    ->circular(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('position')
                    ->searchable(),
                IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
