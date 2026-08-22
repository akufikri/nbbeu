<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Jobs\ImportMembersJob;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

class ImportUsers extends Page
{
    protected static string $resource = UserResource::class;

    protected string $view = 'filament.resources.users.pages.import-users';

    public array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('file')
                    ->label('Excel / CSV File')
                    ->required()
                    ->rules(['mimes:xlsx,xls,csv'])
                    ->disk('local')
                    ->directory('imports/temp')
                    ->visibility('private'),
            ])
            ->statePath('data');
    }

    public function import(): void
    {
        $state    = $this->form->getState();
        $filePath = $state['file'] ?? null;

        if (! $filePath || ! Storage::disk('local')->exists($filePath)) {
            Notification::make()->title('Please upload a file first.')->warning()->send();
            return;
        }

        ImportMembersJob::dispatch($filePath);

        Notification::make()
            ->title('Import queued — members will be processed in background')
            ->success()
            ->send();

        $this->redirect(route('filament.admin.resources.users.index'));
    }
}
