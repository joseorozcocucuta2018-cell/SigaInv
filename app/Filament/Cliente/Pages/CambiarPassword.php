<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Pages;

use App\Models\Cliente;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Página para forzar el cambio de contraseña en el primer login
 * del portal /clientes (Tarea 8.10).
 *
 * Reglas:
 *  - Si el cliente tiene password temporal (password_changed_at = null),
 *    se muestra este formulario SIEMPRE al autenticarse (ver Login
 *    getRedirectUrl + middleware ClienteForzarCambioPassword).
 *    En este caso NO se pide la contraseña actual: el cliente acaba
 *    de autenticarse con la temporal (que le llegó por email), por
 *    lo que ya demostró conocerla. Pedirla de nuevo es mala UX y
 *    además propaga fallos si la implementación del hash cambia.
 *  - Si el cliente ya tiene password definitivo (password_changed_at
 *    != null), puede acceder a la página desde su perfil (sidebar
 *    → Cambiar Contraseña) y SÍ debe confirmar la contraseña actual
 *    con Hash::check.
 *  - Caso borde: si el cliente no tiene password (no debería pasar,
 *    porque tieneAccesoPortal lo bloquearía), se permite setear una
 *    nueva sin pedir la actual.
 */
class CambiarPassword extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-key';

    protected static string|\UnitEnum|null $navigationGroup = 'Cuenta';

    protected static ?string $navigationLabel = 'Cambiar Contraseña';

    protected static ?string $title = 'Cambiar Contraseña';

    protected static ?int $navigationSort = 20;

    protected string $view = 'filament.cliente.pages.cambiar-password';

    /**
     * @var array<string, mixed>
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    /**
     * Determina si se debe pedir la contraseña actual.
     *
     * Solo se pide si el cliente tiene una contraseña definitiva
     * (password_changed_at != null). Si es temporal o no tiene,
     * se omite.
     */
    protected function requiereActual(): bool
    {
        $cliente = $this->cliente();

        if (! $cliente instanceof Cliente) {
            return false;
        }

        if (empty($cliente->password)) {
            return false;
        }

        return $cliente->password_changed_at !== null;
    }

    public function form(Schema $schema): Schema
    {
        $requiereActual = $this->requiereActual();

        return $schema
            ->components([
                TextInput::make('current_password')
                    ->label('Contraseña actual')
                    ->password()
                    ->revealable()
                    ->required($requiereActual)
                    ->visible($requiereActual)
                    ->dehydrated(false)
                    ->helperText($requiereActual ? 'Ingresa tu contraseña actual para confirmar el cambio.' : 'Como esta es tu contraseña temporal, solo necesitas establecer la nueva.')
                    ->validationMessages([
                        'required' => 'Ingresa tu contraseña actual.',
                    ]),

                TextInput::make('password')
                    ->label('Nueva contraseña')
                    ->password()
                    ->revealable()
                    ->required()
                    ->minLength(8)
                    ->maxLength(255)
                    ->same('password_confirmation')
                    ->helperText('Mínimo 8 caracteres.'),

                TextInput::make('password_confirmation')
                    ->label('Confirmar nueva contraseña')
                    ->password()
                    ->revealable()
                    ->required()
                    ->dehydrated(false)
                    ->maxLength(255),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $cliente = $this->cliente();

        if (! $cliente instanceof Cliente) {
            Notification::make()
                ->title('Sesión inválida')
                ->body('No se pudo identificar al cliente autenticado. Vuelve a iniciar sesión.')
                ->danger()
                ->send();

            return;
        }

        $data = $this->form->getState();

        if ($this->requiereActual()) {
            $current = (string) ($data['current_password'] ?? '');

            if ($current === '' || ! Hash::check($current, (string) $cliente->password)) {
                throw ValidationException::withMessages([
                    'data.current_password' => 'La contraseña actual es incorrecta.',
                ]);
            }
        }

        $cliente->forceFill([
            'password' => Hash::make((string) $data['password']),
            'password_changed_at' => now(),
        ])->save();

        Notification::make()
            ->title('Contraseña actualizada')
            ->body('Tu nueva contraseña ha sido guardada exitosamente.')
            ->success()
            ->send();

        $this->redirectRoute('filament.clientes.pages.cliente-dashboard');
    }

    protected function cliente(): ?Cliente
    {
        $user = Auth::guard('cliente')->user();

        return $user instanceof Cliente ? $user : null;
    }
}
