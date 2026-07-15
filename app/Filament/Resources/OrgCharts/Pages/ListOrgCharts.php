<?php

namespace App\Filament\Resources\OrgCharts\Pages;

use App\Filament\Resources\OrgCharts\OrgChartResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOrgCharts extends ListRecords
{
    protected static string $resource = OrgChartResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
