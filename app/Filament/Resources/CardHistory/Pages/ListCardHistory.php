<?php

namespace App\Filament\Resources\CardHistory\Pages;

use App\Filament\Resources\CardHistory\CardHistoryResource;
use Filament\Resources\Pages\ListRecords;

class ListCardHistory extends ListRecords
{
    protected static string $resource = CardHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
