<?php

namespace App\Filament\Resources\CertificateHistory;

use App\Filament\Resources\CertificateHistory\Pages\ListCertificateHistory;
use App\Filament\Resources\CertificateHistory\Tables\CertificateHistoryTable;
use App\Models\Certificate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CertificateHistoryResource extends Resource
{
    protected static ?string $model = Certificate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'Certificate History';

    protected static ?string $slug = 'certificate-history';

    protected static string|\UnitEnum|null $navigationGroup = 'Card & Certificate';

    public static function table(Table $table): Table
    {
        return CertificateHistoryTable::configure($table);
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
            'index' => ListCertificateHistory::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
