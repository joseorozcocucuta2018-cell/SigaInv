<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use App\Enums\UserEstado;
use App\Models\User;
use App\Notifications\RegistroPendienteNotification;
use App\Notifications\SolicitudAccesoNotification;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Auth\Http\Responses\Contracts\RegistrationResponse;
use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class Register extends BaseRegister
{
    public function form(Schema $schema): Schema
    {
        return $schema->components([
            $this->getNameFormComponent(),
            $this->getEmailFormComponent(),
            TextInput::make('cargo')
                ->label('Cargo / Motivo de acceso')
                ->placeholder('Ej: Vendedor, Contador, Revisión de inventario...')
                ->required()
                ->maxLength(100),
            $this->getPasswordFormComponent(),
            $this->getPasswordConfirmationFormComponent(),
        ]);
    }

    protected function mutateFormDataBeforeRegister(array $data): array
    {
        $data['estado'] = UserEstado::PENDIENTE;

        return $data;
    }

    public function register(): ?RegistrationResponse
    {
        try {
            $this->rateLimit(2);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $user = $this->wrapInDatabaseTransaction(function (): Model {
            $this->callHook('beforeValidate');

            $data = $this->form->getState();

            $this->callHook('afterValidate');

            $data = $this->mutateFormDataBeforeRegister($data);

            $this->callHook('beforeRegister');

            $user = $this->handleRegistration($data);

            $this->form->model($user)->saveRelationships();

            $this->callHook('afterRegister');

            return $user;
        });

        // Notificar al nuevo usuario (correo de confirmación de solicitud recibida)
        try {
            $user->notify(new RegistroPendienteNotification);
        } catch (\Exception $e) {
            logger()->warning('No se pudo enviar correo al nuevo usuario: '.$e->getMessage());
        }

        // Notificar a todos los administradores (DB + email)
        User::role('administrador')->get()->each(function ($admin) use ($user) {
            try {
                $admin->notify(new SolicitudAccesoNotification($user));
            } catch (\Exception $e) {
                logger()->warning("No se pudo notificar al admin {$admin->email}: ".$e->getMessage());
            }
        });

        // Flash message para que aparezca en el login
        session()->flash(
            'registro_pendiente',
            'Tu solicitud de acceso ha sido enviada. Un administrador aprobará tu cuenta y recibirás acceso al sistema.'
        );

        $this->redirect(filament()->getLoginUrl());

        return null;
    }
}
