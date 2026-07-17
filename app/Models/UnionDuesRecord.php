<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['mandate_id', 'period_month', 'amount_received', 'recorded_by', 'recorded_at', 'notes'])]
class UnionDuesRecord extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'amount_received' => 'decimal:2',
            'recorded_at' => 'datetime',
        ];
    }

    public function mandate(): BelongsTo
    {
        return $this->belongsTo(UnionDuesMandate::class, 'mandate_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
