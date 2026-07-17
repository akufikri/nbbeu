<?php

namespace App\Filament\Resources\OrgCharts\Tables;

use App\Models\OrgChart;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class OrgChartsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('display_order')
            ->reorderable('display_order')
            ->headerActions([
                Action::make('preview')
                    ->label('Preview')
                    ->icon(Heroicon::OutlinedEye)
                    ->color('gray')
                    ->modalHeading('Org Chart Preview')
                    ->modalContent(fn () => view('filament.resources.org-charts.preview', [
                        'roots' => static::buildTree(),
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
            ])
            ->columns([
                ImageColumn::make('photo')
                    ->disk('cloudinary')
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

    protected static function buildTree(): array
    {
        $all = OrgChart::orderBy('display_order')->get();
        $roots = $all->whereNull('parent_id');

        return $roots->map(fn ($node) => static::toNode($node, $all))->values()->all();
    }

    protected static function toNode(OrgChart $node, Collection $all): array
    {
        $children = $all->where('parent_id', $node->id)->sortBy('display_order');

        return [
            'name' => $node->name,
            'title' => $node->position,
            'img' => $node->photo ? Storage::disk('cloudinary')->url($node->photo) : null,
            'className' => $node->is_active ? '' : 'inactive-node',
            'children' => $children->map(fn ($child) => static::toNode($child, $all))->values()->all(),
        ];
    }
}
