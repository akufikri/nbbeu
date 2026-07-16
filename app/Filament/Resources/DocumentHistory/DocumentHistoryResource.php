<?php

namespace App\Filament\Resources\DocumentHistory;

use App\Filament\Resources\DocumentHistory\Pages\ListDocumentHistory;
use App\Filament\Resources\DocumentHistory\Tables\DocumentHistoryTable;
use App\Models\MemberCard;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DocumentHistoryResource extends Resource
{
    protected static ?string $model = MemberCard::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'Document History';

    protected static ?string $slug = 'document-history';

    protected static string|\UnitEnum|null $navigationGroup = 'Card & Certificate';

    public static function table(Table $table): Table
    {
        return DocumentHistoryTable::configure($table);
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
            'index' => ListDocumentHistory::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
