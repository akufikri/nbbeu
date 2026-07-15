<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'position', 'photo', 'display_order', 'is_active'])]
class OrgChart extends Model
{
    protected $table = 'org_chart';

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
