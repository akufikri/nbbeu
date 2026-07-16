<?php

namespace App\Filament\Resources\Posts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use App\Models\Post;
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
                Select::make('category')
                    ->placeholder('Select category')
                    ->options(Post::CATEGORIES),
                Textarea::make('excerpt')
                    ->placeholder('Short summary shown in the blog listing')
                    ->maxLength(500)
                    ->columnSpanFull(),
                RichEditor::make('content')
                    ->required()
                    ->columnSpanFull(),
                FileUpload::make('cover_image')
                    ->label('Thumbnail Image')
                    ->helperText('Used in blog listing cards. Recommended 800×600px (4:3).')
                    ->image()
                    ->imageResizeMode('cover')
                    ->imageResizeTargetWidth('800')
                    ->imageResizeTargetHeight('600')
                    ->maxSize(2048)
                    ->disk('public')
                    ->directory('posts/thumbnails'),
                FileUpload::make('hero_image')
                    ->label('Main Image')
                    ->helperText('Used as the article hero banner. Recommended 1600×900px (16:9).')
                    ->image()
                    ->imageResizeMode('cover')
                    ->imageResizeTargetWidth('1600')
                    ->imageResizeTargetHeight('900')
                    ->maxSize(4096)
                    ->disk('public')
                    ->directory('posts/hero'),
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
