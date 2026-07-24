<?php

namespace App\Filament\Resources\Galleries\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class GalleryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            FileUpload::make('image')
                ->image()
                ->imageResizeMode('cover')
                ->imageResizeTargetWidth('1200')
                ->imageResizeTargetHeight('900')
                ->maxSize(4096)
                ->disk('cloudinary')
                ->directory('gallery')
                ->required(),
            TextInput::make('title')
                ->maxLength(255),
            TextInput::make('category')
                ->maxLength(100)
                ->helperText('Optional tag used to group/filter photos, e.g. "Seminar", "CSR".'),
            TextInput::make('display_order')
                ->numeric()
                ->default(0)
                ->required(),
            Toggle::make('is_active')
                ->label('Visible on public site')
                ->default(true),
        ]);
    }
}
