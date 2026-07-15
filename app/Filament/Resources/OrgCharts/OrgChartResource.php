<?php

namespace App\Filament\Resources\OrgCharts;

use App\Filament\Resources\OrgCharts\Pages\CreateOrgChart;
use App\Filament\Resources\OrgCharts\Pages\EditOrgChart;
use App\Filament\Resources\OrgCharts\Pages\ListOrgCharts;
use App\Filament\Resources\OrgCharts\Schemas\OrgChartForm;
use App\Filament\Resources\OrgCharts\Tables\OrgChartsTable;
use App\Models\OrgChart;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OrgChartResource extends Resource
{
    protected static ?string $model = OrgChart::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $navigationLabel = 'Org Chart';

    public static function form(Schema $schema): Schema
    {
        return OrgChartForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrgChartsTable::configure($table);
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
            'index' => ListOrgCharts::route('/'),
            'create' => CreateOrgChart::route('/create'),
            'edit' => EditOrgChart::route('/{record}/edit'),
        ];
    }
}
