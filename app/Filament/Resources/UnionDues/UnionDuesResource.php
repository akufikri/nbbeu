<?php

namespace App\Filament\Resources\UnionDues;

use App\Filament\Resources\UnionDues\Pages\ListUnionDues;
use App\Filament\Resources\UnionDues\Tables\UnionDuesTable;
use App\Models\UnionDuesMandate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UnionDuesResource extends Resource
{
    protected static ?string $model = UnionDuesMandate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $navigationLabel = 'Union Dues';

    public static function table(Table $table): Table
    {
        return UnionDuesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUnionDues::route('/'),
        ];
    }
}
