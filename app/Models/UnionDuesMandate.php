<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'deduction_amount', 'consent_file_path', 'status', 'consent_signed_at', 'cancelled_at'])]
class UnionDuesMandate extends Model
{
    protected function casts(): array
    {
        return [
            'deduction_amount' => 'decimal:2',
            'consent_signed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function records(): HasMany
    {
        return $this->hasMany(UnionDuesRecord::class, 'mandate_id');
    }
}
