<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Filament\Cliente\Pages\Auth\Login;
use App\Filament\Cliente\Pages\CambiarPassword;
use App\Filament\Cliente\Pages\ClienteDashboard;
use App\Filament\Cliente\Pages\EditarPerfil;
use App\Filament\Cliente\Widgets\ClienteStatsWidget;
use App\Filament\Resources\PortalClientes\CotizacionPortals\CotizacionPortalResource;
use App\Filament\Resources\PortalClientes\FacturaPortals\FacturaPortalResource;
use App\Filament\Resources\PortalClientes\RemisionPortals\RemisionPortalResource;
use App\Http\Middleware\ClienteForzarCambioPassword;
use App\Models\Empresa;
use Awcodes\LightSwitch\Enums\Alignment;
use Awcodes\LightSwitch\LightSwitchPlugin;
use Awcodes\QuickCreate\QuickCreatePlugin;
use Filament\FontProviders\GoogleFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
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
use Joseforozco\FilamentAutoLogout\AutoLogoutPlugin;
use Swis\Filament\Backgrounds\FilamentBackgroundsPlugin;

class ClientePanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('clientes')
            ->path('clientes')
            ->login(Login::class)
            ->sidebarCollapsibleOnDesktop()
            ->brandName(fn () => Empresa::actual()?->nombre_comercial ?? 'SIGA - Portal Cliente')
            ->brandLogo(fn () => Empresa::actual()?->logo
                ? Storage::disk('directo')->url(Empresa::actual()->logo)
                : asset('images/sigaweb-logo.svg'))
            ->brandLogoHeight('3rem')
            ->favicon(asset('images/sigaweb-icon.svg'))
            ->font('JetBrains Mono', provider: GoogleFontProvider::class)
            ->colors([
                'primary' => Color::Emerald,
            ])
            ->breadcrumbs(false)
            ->databaseNotifications()
            ->databaseNotificationsPolling('60s')
            ->resources([
                FacturaPortalResource::class,
                RemisionPortalResource::class,
                CotizacionPortalResource::class,
            ])
            ->pages([
                ClienteDashboard::class,
                EditarPerfil::class,
                CambiarPassword::class,
            ])
            ->widgets([
                ClienteStatsWidget::class,
            ])
            ->navigationGroups([
                'Mis Documentos',
                'Cuenta',
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
            ])
            ->authMiddleware([
                Authenticate::class,
                ClienteForzarCambioPassword::class,
            ])
            ->authGuard('cliente')
            ->plugins([
                LightSwitchPlugin::make()
                    ->position(Alignment::TopRight)
                    ->enabledOn([
                        'filament.clientes.auth.login',
                        'clientes.login',
                    ]),
                FilamentBackgroundsPlugin::make(),
                QuickCreatePlugin::make(),
                AutoLogoutPlugin::make()
                    ->timeLeftText('Tiempo restante:'),
            ]);
    }
}
