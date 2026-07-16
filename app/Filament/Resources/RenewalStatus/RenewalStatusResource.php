<?php

namespace App\Filament\Resources\RenewalStatus;

use App\Filament\Resources\RenewalStatus\Pages\ListRenewalStatuses;
use App\Filament\Resources\RenewalStatus\Tables\RenewalStatusTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RenewalStatusResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $slug = 'renewal-status';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPath;

    protected static ?string $navigationLabel = 'Renewal Status';

    protected static ?string $modelLabel = 'member renewal';

    protected static ?string $pluralModelLabel = 'renewal status';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('status', 'approved');
    }

    public static function table(Table $table): Table
    {
        return RenewalStatusTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRenewalStatuses::route('/'),
        ];
    }
}
