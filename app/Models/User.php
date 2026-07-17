<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'phone', 'company', 'password', 'photo'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'approved_at' => 'datetime',
            'renewal_expires_at' => 'date',
        ];
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function memberCards(): HasMany
    {
        return $this->hasMany(MemberCard::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function memberProfile(): HasOne
    {
        return $this->hasOne(MemberProfile::class);
    }

    public function unionDuesMandates(): HasMany
    {
        return $this->hasMany(UnionDuesMandate::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'author_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdmin() && $this->is_active;
    }
}
