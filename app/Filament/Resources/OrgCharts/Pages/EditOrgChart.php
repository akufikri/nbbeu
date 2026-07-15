<?php

namespace App\Filament\Resources\OrgCharts\Pages;

use App\Filament\Resources\OrgCharts\OrgChartResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOrgChart extends EditRecord
{
    protected static string $resource = OrgChartResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
