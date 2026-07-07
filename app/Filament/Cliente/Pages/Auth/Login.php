<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Pages\Auth;

use App\Enums\ClienteEstado;
use App\Models\Cliente;
use App\Services\PortalAccesoService;
use Filament\Actions\Action;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Schema;
use Illuminate\Cache\RateLimiter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Login del panel de clientes (/clientes) — Tarea 8.07.
 *
 * El panel autentica clientes directamente desde la tabla `clientes`
 * usando el guard 'cliente' y el provider 'clientes' (config/auth.php).
 * El alta de cuentas NO es autoservicio: el admin genera la contraseña
 * desde ClienteResource (acción "Generar contraseña de acceso").
 *
 * Reglas de acceso:
 *  - Cliente debe existir con email único.
 *  - Estado del cliente = ACTIVO.
 *  - Cliente debe tener password seteado (sino: "contacta al admin").
 *  - Si password_changed_at = null → redirige a /clientes/cambiar-password.
 */
class Login extends BaseLogin
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
                Actions::make([
                    Action::make('olvidastePassword')
                        ->label('¿Olvidaste tu contraseña?')
                        ->link()
                        ->modalHeading('Recuperar contraseña')
                        ->modalDescription('Ingresa tu correo electrónico. Si tienes acceso al portal, recibirás una contraseña temporal.')
                        ->modalSubmitActionLabel('Enviar')
                        ->modalWidth('sm')
                        ->form([
                            TextInput::make('email')
                                ->label('Correo electrónico')
                                ->email()
                                ->required()
                                ->autocomplete('email'),
                        ])
                        ->action(function (array $data): void {
                            $this->enviarPasswordRecuperacion($data['email']);
                        }),
                ]),
            ]);
    }

    protected function enviarPasswordRecuperacion(string $email): void
    {
        $this->verificarRateLimit();

        $cliente = Cliente::query()->where('email', $email)->first();

        if ($cliente instanceof Cliente
            && $cliente->estado === ClienteEstado::ACTIVO
            && app(PortalAccesoService::class)->puedeTenerAcceso($cliente)
        ) {
            app(PortalAccesoService::class)->generarYEnviarPassword($cliente);
        }

        Notification::make()
            ->title('Revisa tu correo')
            ->body('Si el correo está registrado con acceso al portal, recibirás instrucciones para acceder.')
            ->success()
            ->send();
    }

    protected function verificarRateLimit(): void
    {
        $key = 'olvidaste-password:'.request()->ip();

        /** @var RateLimiter $rateLimiter */
        $rateLimiter = app(RateLimiter::class);

        if ($rateLimiter->tooManyAttempts($key, 5)) {
            $seconds = $rateLimiter->availableIn($key);

            Notification::make()
                ->title('Demasiados intentos')
                ->body("Intenta de nuevo en {$seconds} segundos.")
                ->danger()
                ->send();

            return;
        }

        $rateLimiter->hit($key, 600);
    }

    public function authenticate(): ?LoginResponse
    {
        $data = $this->form->getState();
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if (! $email || ! $password) {
            throw ValidationException::withMessages([
                'data.email' => 'Ingresa tu correo y contraseña.',
            ]);
        }

        $cliente = Cliente::query()->where('email', $email)->first();

        if ($cliente === null) {
            throw ValidationException::withMessages([
                'data.email' => 'Estas credenciales no coinciden con nuestros registros.',
            ]);
        }

        if ($cliente->estado !== ClienteEstado::ACTIVO) {
            throw ValidationException::withMessages([
                'data.email' => 'Tu cuenta está inactiva. Contacta al administrador.',
            ]);
        }

        if (! $cliente->tieneAccesoPortal()) {
            throw ValidationException::withMessages([
                'data.email' => 'No tienes acceso al portal. Contacta al administrador.',
            ]);
        }

        if (! Hash::check($password, (string) $cliente->password)) {
            throw ValidationException::withMessages([
                'data.email' => 'Estas credenciales no coinciden con nuestros registros.',
            ]);
        }

        $remember = (bool) ($data['remember'] ?? false);
        Auth::guard('cliente')->login($cliente, $remember);

        $cliente->forceFill(['portal_last_login_at' => now()])->save();

        return app(LoginResponse::class);
    }

    protected function getRedirectUrl(): string
    {
        $cliente = Auth::guard('cliente')->user();

        if ($cliente instanceof Cliente && $cliente->debeCambiarPassword()) {
            return route('filament.clientes.pages.cambiar-password');
        }

        return route('filament.clientes.pages.cliente-dashboard');
    }
}
