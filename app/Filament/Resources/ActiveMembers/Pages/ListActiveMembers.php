<?php

namespace App\Filament\Resources\ActiveMembers\Pages;

use App\Exports\UsersTemplateExport;
use App\Filament\Resources\ActiveMembers\ActiveMembersResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Maatwebsite\Excel\Facades\Excel;

class ListActiveMembers extends ListRecords
{
    protected static string $resource = ActiveMembersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadTemplate')
                ->label('Download Excel Template')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->color('gray')
                ->action(fn () => Excel::download(new UsersTemplateExport, 'nbbeu-members-import-template.xlsx')),
        ];
    }
}
