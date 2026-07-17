<?php

namespace App\Filament\Resources\Posts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use App\Models\Post;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('cover_image')
                    ->disk('cloudinary'),
                TextColumn::make('title')
                    ->searchable()
                    ->weight('medium'),
                TextColumn::make('author.name')
                    ->searchable(),
                TextColumn::make('category')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => $state ? Post::CATEGORIES[$state] ?? $state : '-'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'published' ? 'success' : 'gray'),
                TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('-'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->placeholder('All statuses')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                    ]),
                SelectFilter::make('category')
                    ->placeholder('All categories')
                    ->options(Post::CATEGORIES),
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
