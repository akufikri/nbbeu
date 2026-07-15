<?php

namespace App\Filament\Resources\OrgCharts\Pages;

use App\Filament\Resources\OrgCharts\OrgChartResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOrgChart extends CreateRecord
{
    protected static string $resource = OrgChartResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
