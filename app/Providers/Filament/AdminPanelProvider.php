<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Auth\Register;
use App\Filament\Widgets\ResumenGeneralWidget;
use App\Filament\Widgets\StockBajoWidget;
use App\Filament\Widgets\UltimasVentasWidget;
use App\Http\Middleware\VerificarEmpresaConfigurada;
use App\Models\Empresa;
use Ariefng\FilamentCalculator\CalculatorPlugin;
use Awcodes\LightSwitch\Enums\Alignment;
use Awcodes\LightSwitch\LightSwitchPlugin;
use Awcodes\QuickCreate\QuickCreatePlugin;
use AzGasim\FilamentUnsavedChangesModal\FilamentUnsavedChangesModalPlugin;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\FontProviders\GoogleFontProvider;
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
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Joaopaulolndev\FilamentEditProfile\FilamentEditProfilePlugin;
use Joseforozco\FilamentAutoLogout\AutoLogoutPlugin;
use Relaticle\ActivityLog\ActivityLogPlugin;
use Swis\Filament\Backgrounds\FilamentBackgroundsPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->sidebarCollapsibleOnDesktop()
            ->login(Login::class)
            ->registration(Register::class)
            ->brandName(fn () => Empresa::actual()?->nombre_comercial ?? 'SigaWeb')
            ->brandLogo(fn () => Empresa::actual()?->logo
                ? Storage::disk('directo')->url(Empresa::actual()->logo)
                : asset('images/sigaweb-logo.svg'))
            ->brandLogoHeight('4rem')
            ->favicon(asset('images/sigaweb-icon.svg'))
            ->font('JetBrains Mono', provider: GoogleFontProvider::class)
            ->colors([
                'primary' => Color::Amber,
            ])
            ->MultiFactorAuthentication([
                AppAuthentication::make()->recoverable(),
            ], isRequired: filter_var(env('MFA_REQUIRED', false), FILTER_VALIDATE_BOOLEAN)
            )
            ->breadcrumbs(false)
            ->databaseNotifications()
            ->databaseNotificationsPolling('60s')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                ResumenGeneralWidget::class,
                StockBajoWidget::class,
                UltimasVentasWidget::class,
            ])
            ->navigationGroups([
                'Administración',
                'Configuración',
                'Inventario',
                'Fórmulas de Transformación',
                'Caja',
                'Bancos',
                'Compras',
                'Ventas',
                'Reportes',
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                VerificarEmpresaConfigurada::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugins([
                LightSwitchPlugin::make()
                    ->position(Alignment::TopRight),
                FilamentBackgroundsPlugin::make(),
                QuickCreatePlugin::make(),
                AutoLogoutPlugin::make()
                    ->timeLeftText('Tiempo restante:'),
                FilamentEditProfilePlugin::make()
                    ->shouldShowDeleteAccountForm(false)
                    ->shouldShowBrowserSessionsForm()
                    ->shouldShowAvatarForm(
                        value: true,
                        directory: 'avatars',
                        rules: 'mimes:jpeg,png|max:1024'
                    )
                    ->shouldShowMultiFactorAuthentication()
                    ->shouldRegisterNavigation()
                    ->shouldShowEmailForm(),
                FilamentUnsavedChangesModalPlugin::make(),
                ActivityLogPlugin::make(),
                CalculatorPlugin::make(),
            ]);
    }
}
