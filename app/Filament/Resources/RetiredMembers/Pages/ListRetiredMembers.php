<?php

namespace App\Filament\Resources\RetiredMembers\Pages;

use App\Filament\Resources\RetiredMembers\RetiredMembersResource;
use Filament\Resources\Pages\ListRecords;

class ListRetiredMembers extends ListRecords
{
    protected static string $resource = RetiredMembersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
