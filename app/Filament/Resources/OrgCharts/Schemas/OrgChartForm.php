<?php

namespace App\Filament\Resources\OrgCharts\Schemas;

use App\Models\OrgChart;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class OrgChartForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->placeholder('Full name')
                    ->required(),
                TextInput::make('position')
                    ->placeholder('Position, e.g. President')
                    ->required(),
                Select::make('parent_id')
                    ->label('Reports To')
                    ->placeholder('None (top of chart)')
                    ->options(fn ($record) => OrgChart::query()
                        ->when($record, fn ($q) => $q->whereKeyNot($record->id))
                        ->pluck('name', 'id'))
                    ->searchable(),
                FileUpload::make('photo')
                    ->image()
                    ->imageResizeMode('cover')
                    ->imageResizeTargetWidth('600')
                    ->imageResizeTargetHeight('600')
                    ->maxSize(2048)
                    ->disk('cloudinary')
                    ->directory('org-chart'),
                Toggle::make('is_active')
                    ->default(true)
                    ->required(),
                TextInput::make('display_order')
                    ->numeric()
                    ->default(fn () => (OrgChart::max('display_order') ?? 0) + 1)
                    ->hidden()
                    ->dehydrated(),
            ]);
    }
}
