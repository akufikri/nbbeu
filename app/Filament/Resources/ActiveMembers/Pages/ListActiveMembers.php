<?php

namespace App\Filament\Resources\ActiveMembers\Pages;

use App\Filament\Resources\ActiveMembers\ActiveMembersResource;
use Filament\Resources\Pages\ListRecords;

class ListActiveMembers extends ListRecords
{
    protected static string $resource = ActiveMembersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
