<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\UserEstado;
use App\Models\User;
use App\Notifications\AccesoAprobadoNotification;
use App\Notifications\CuentaSuspendidaNotification;

class UserService
{
    public function desactivar(User $user): void
    {
        $user->update(['estado' => UserEstado::INACTIVO]);

        try {
            $user->notify(new CuentaSuspendidaNotification);
        } catch (\Exception) {
            // No bloquear si el correo falla
        }
    }

    public function aprobar(User $user, string $rol): void
    {
        $user->update(['estado' => UserEstado::ACTIVO]);
        $user->syncRoles([$rol]);

        try {
            $user->notify(new AccesoAprobadoNotification);
        } catch (\Exception $e) {
            logger()->warning("No se pudo notificar al usuario {$user->email}: ".$e->getMessage());
        }
    }

    public function rechazar(User $user): void
    {
        $user->delete();
    }
}
