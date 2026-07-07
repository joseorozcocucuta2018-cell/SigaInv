<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use App\Enums\UserEstado;
use App\Models\User;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    public function mount(): void
    {
        parent::mount();

        if (session()->has('registro_pendiente')) {
            Notification::make()
                ->title('Solicitud enviada exitosamente')
                ->body(session()->pull('registro_pendiente'))
                ->success()
                ->persistent()
                ->send();
        }
    }

    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getEmailFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getRememberFormComponent(),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    public function authenticate(): ?LoginResponse
    {
        // Verificar si el usuario tiene credenciales válidas pero la cuenta está inactiva
        $email = $this->data['email'] ?? null;
        $password = $this->data['password'] ?? null;

        if ($email && $password) {
            $user = User::where('email', $email)->first();

            if ($user && $user->estado !== UserEstado::ACTIVO && Hash::check($password, $user->password)) {
                $mensaje = match ($user->estado) {
                    UserEstado::PENDIENTE => 'Tu cuenta está pendiente de aprobación por un administrador.',
                    UserEstado::INACTIVO => 'Tu cuenta ha sido desactivada. Contacta al administrador para reactivarla.',
                    UserEstado::BLOQUEADO => 'Tu cuenta ha sido bloqueada. Contacta al administrador.',
                    default => 'Tu cuenta no está activa. Contacta al administrador.',
                };

                throw ValidationException::withMessages([
                    'data.email' => $mensaje,
                ]);
            }
        }

        return parent::authenticate();
    }
}
