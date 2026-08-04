<?php

namespace App\Filament\Resources\RetiredMembers\Pages;

use App\Filament\Resources\RetiredMembers\RetiredMembersResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewRetiredMember extends ViewRecord
{
    protected static string $resource = RetiredMembersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
