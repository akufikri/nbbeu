<?php

namespace App\Filament\Resources\RetiredMembers\Pages;

use App\Filament\Resources\RetiredMembers\RetiredMembersResource;
use Filament\Resources\Pages\EditRecord;

class EditRetiredMember extends EditRecord
{
    protected static string $resource = RetiredMembersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
