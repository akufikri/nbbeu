<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class SiteSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Site Settings';

    protected static string|\UnitEnum|null $navigationGroup = 'System';

    protected string $view = 'filament.pages.site-settings';

    /**
     * @var array<string, mixed>
     */
    public array $data = [];

    /**
     * @var array<int, string>
     */
    protected static array $settingKeys = [
        'contact_email',
        'contact_phone',
        'contact_address',
        'social_facebook',
        'social_linkedin',
        'social_instagram',
        'footer_text',
        'how_to_join_text',
        'card_logo',
        'card_signature',
        'collective_agreement_text',
    ];

    public function mount(): void
    {
        $values = [];

        foreach (static::$settingKeys as $key) {
            $values[$key] = Setting::get($key);
        }

        $this->form->fill($values);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Settings')
                    ->tabs([
                        Tab::make('Contact & Social')
                            ->schema([
                                Section::make('Contact Information')
                                    ->schema([
                                        TextInput::make('contact_email')
                                            ->email()
                                            ->placeholder('info@nbbeu.org'),
                                        TextInput::make('contact_phone')
                                            ->tel()
                                            ->placeholder('+60 12-345 6789'),
                                        Textarea::make('contact_address')
                                            ->placeholder('Union office address'),
                                    ]),
                                Section::make('Social Media')
                                    ->schema([
                                        TextInput::make('social_facebook')
                                            ->url()
                                            ->placeholder('https://facebook.com/...'),
                                        TextInput::make('social_linkedin')
                                            ->url()
                                            ->placeholder('https://linkedin.com/...'),
                                        TextInput::make('social_instagram')
                                            ->url()
                                            ->placeholder('https://instagram.com/...'),
                                    ]),
                                Section::make('Footer & Join Page')
                                    ->schema([
                                        Textarea::make('footer_text')
                                            ->placeholder('Footer text shown on the public site'),
                                        Textarea::make('how_to_join_text')
                                            ->label('How to Join Text')
                                            ->rows(6)
                                            ->placeholder('Text shown in the "How to Join" section on the public home page'),
                                    ]),
                            ]),
                        Tab::make('Card & Certificate Branding')
                            ->schema([
                                FileUpload::make('card_logo')
                                    ->image()
                                    ->maxSize(2048)
                                    ->disk('cloudinary')
                                    ->directory('branding')
                                    ->helperText('Recommended size: 500x500px, square, transparent PNG. Max 2MB.'),
                                FileUpload::make('card_signature')
                                    ->image()
                                    ->maxSize(2048)
                                    ->disk('cloudinary')
                                    ->directory('branding')
                                    ->helperText('Recommended size: 400x150px, transparent PNG. Max 2MB.'),
                            ]),
                        Tab::make('Collective Agreement')
                            ->schema([
                                Textarea::make('collective_agreement_text')
                                    ->label('Collective Agreement Text')
                                    ->rows(12)
                                    ->placeholder('Text shown on the public Collective Agreement page'),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $values = $this->form->getState();

        foreach ($values as $key => $value) {
            Setting::set($key, $value);
        }

        Notification::make()
            ->success()
            ->title('Settings saved')
            ->send();
    }
}
