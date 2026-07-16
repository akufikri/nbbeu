<?php

namespace App\Filament\Resources\DocumentHistory\Pages;

use App\Filament\Resources\DocumentHistory\DocumentHistoryResource;
use Filament\Resources\Pages\ListRecords;

class ListDocumentHistory extends ListRecords
{
    protected static string $resource = DocumentHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
