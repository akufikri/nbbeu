<?php

namespace App\Filament\Resources\CertificateHistory\Pages;

use App\Filament\Resources\CertificateHistory\CertificateHistoryResource;
use Filament\Resources\Pages\ListRecords;

class ListCertificateHistory extends ListRecords
{
    protected static string $resource = CertificateHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
