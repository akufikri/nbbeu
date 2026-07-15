<?php

namespace App\Filament\Resources\Posts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('author_id')
                    ->placeholder('Select author')
                    ->relationship('author', 'name')
                    ->default(fn () => Auth::id())
                    ->required(),
                TextInput::make('title')
                    ->placeholder('Post title')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, $set) => $set('slug', Str::slug($state))),
                TextInput::make('slug')
                    ->placeholder('post-title')
                    ->required()
                    ->unique(ignoreRecord: true),
                Textarea::make('excerpt')
                    ->placeholder('Short summary shown in the blog listing')
                    ->maxLength(500)
                    ->columnSpanFull(),
                RichEditor::make('content')
                    ->required()
                    ->columnSpanFull(),
                FileUpload::make('cover_image')
                    ->image()
                    ->imageResizeMode('cover')
                    ->imageResizeTargetWidth('1200')
                    ->imageResizeTargetHeight('675')
                    ->maxSize(2048)
                    ->disk('public')
                    ->directory('posts'),
                Select::make('status')
                    ->placeholder('Select status')
                    ->options(['draft' => 'Draft', 'published' => 'Published'])
                    ->default('draft')
                    ->live()
                    ->required(),
                DateTimePicker::make('published_at')
                    ->default(now())
                    ->visible(fn ($get) => $get('status') === 'published'),
            ]);
    }
}
