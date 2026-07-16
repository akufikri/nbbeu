<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['author_id', 'title', 'slug', 'excerpt', 'category', 'content', 'cover_image', 'hero_image', 'status', 'published_at'])]
class Post extends Model
{
    public const CATEGORIES = [
        'regulation' => 'Regulation',
        'cybersecurity' => 'Cybersecurity',
        'symposium' => 'Symposium',
        'certification' => 'Certification',
        'reports' => 'Reports',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
