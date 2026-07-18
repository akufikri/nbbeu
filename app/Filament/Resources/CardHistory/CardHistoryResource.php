<?php

namespace App\Filament\Resources\CardHistory;

use App\Filament\Resources\CardHistory\Pages\ListCardHistory;
use App\Filament\Resources\CardHistory\Tables\CardHistoryTable;
use App\Models\MemberCard;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CardHistoryResource extends Resource
{
    protected static ?string $model = MemberCard::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    protected static ?string $navigationLabel = 'Card History';

    protected static ?string $slug = 'card-history';

    protected static string|\UnitEnum|null $navigationGroup = 'Card & Certificate';

    public static function table(Table $table): Table
    {
        return CardHistoryTable::configure($table);
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
            'index' => ListCardHistory::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
