<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\ExpiringMembersWidget;
use App\Filament\Widgets\MembershipOverview;
use App\Filament\Widgets\MembershipStatusChart;
use App\Filament\Widgets\MembersTrendChart;
use Filament\Enums\ThemeMode;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function boot(): void
    {
        // Registered here (not a plain <script> tag) because Filament modal
        // content is injected into the DOM by Livewire, and browsers never
        // execute <script> tags added that way — only assets present since
        // initial page load actually run.
        FilamentAsset::register([
            Js::make('orgchart-jquery', 'https://code.jquery.com/jquery-3.7.1.min.js'),
            Js::make('orgchart-plugin', asset('assets/vendor/orgchart/jquery.orgchart.min.js')),
            Css::make('orgchart-styles', asset('assets/vendor/orgchart/jquery.orgchart.min.css')),
        ]);
    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->authGuard('admin')
            ->defaultThemeMode(ThemeMode::Light)
            ->brandLogo(asset('assets/images/logo.png'))
            ->brandLogoHeight('2.5rem')
            ->favicon(asset('assets/images/logo.png'))
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                MembershipOverview::class,
                MembersTrendChart::class,
                MembershipStatusChart::class,
                ExpiringMembersWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
