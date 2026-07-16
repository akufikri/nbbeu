<?php

namespace App\Filament\Resources\ActiveMembers\Pages;

use App\Filament\Resources\ActiveMembers\ActiveMembersResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewActiveMember extends ViewRecord
{
    protected static string $resource = ActiveMembersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
