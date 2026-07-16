<?php

namespace App\Filament\Resources\ActiveMembers\Pages;

use App\Filament\Resources\ActiveMembers\ActiveMembersResource;
use Filament\Resources\Pages\EditRecord;

class EditActiveMember extends EditRecord
{
    protected static string $resource = ActiveMembersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
