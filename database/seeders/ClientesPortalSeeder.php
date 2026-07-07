<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Mail\PortalCredencialesMail;
use App\Models\Cliente;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * Seeder único para la migración del portal a Cliente Authenticatable
 * (Tarea 8.05 + 8.06 del Tasklist).
 *
 * EJECUCIÓN: este seeder NO se llama desde DatabaseSeeder (es one-shot).
 * El usuario lo corre manualmente:
 *   php artisan db:seed --class=ClientesPortalSeeder
 *
 * Acciones:
 *  1. Genera password temporal para cada cliente con portal_acceso=activo
 *     y email real (no el placeholder `no_tiene_correo@correo.com`).
 *  2. Marca password_changed_at=null → fuerza cambio en primer login.
 *  3. Encola email con credenciales vía PortalCredencialesMail.
 *  4. Revoca el rol 'cliente' de cualquier User que lo tuviera (mantiene
 *     al User para FK de auditorías, pero ya no puede acceder al portal).
 *  5. Idempotente: si un cliente ya tiene password, lo omite.
 */
class ClientesPortalSeeder extends Seeder
{
    public function run(): void
    {
        $this->migrarClientesExistentes();
        $this->revocarRolClienteDeUsers();
    }

    /**
     * Asigna password temporal a clientes con portal_acceso=activo.
     */
    private function migrarClientesExistentes(): void
    {
        $clientes = Cliente::query()
            ->where('portal_acceso', 'activo')
            ->where('email', '!=', 'no_tiene_correo@correo.com')
            ->get();

        if ($clientes->isEmpty()) {
            $this->command?->info('No hay clientes con portal_acceso=activo para migrar.');

            return;
        }

        foreach ($clientes as $cliente) {
            if (! empty($cliente->password)) {
                $this->command?->info("Cliente #{$cliente->id} ({$cliente->email}) ya tiene password. Omitido.");

                continue;
            }

            $passwordPlano = Str::random(12);

            $cliente->forceFill([
                'password' => Hash::make($passwordPlano),
                'password_changed_at' => null,
                'remember_token' => Str::random(60),
            ])->save();

            Mail::to($cliente->email)->queue(
                new PortalCredencialesMail($cliente, $passwordPlano)
            );

            Log::info("Portal migrado para cliente #{$cliente->id}", [
                'cliente_id' => $cliente->id,
                'email' => $cliente->email,
            ]);

            $this->command?->info("Cliente #{$cliente->id} ({$cliente->email}): password temporal generada y email encolado.");
        }
    }

    /**
     * Revoca el rol 'cliente' de los Users — el portal ya no usa el modelo
     * User con doble rol. Los Users se conservan (FK de auditorías), pero
     * quedan inertes para el acceso al portal.
     */
    private function revocarRolClienteDeUsers(): void
    {
        $rol = Role::findByName('cliente');

        if ($rol === null) {
            $this->command?->warn("El rol 'cliente' no existe en la base de datos. Omitido.");

            return;
        }

        $count = $rol->users()->count();

        if ($count === 0) {
            $this->command?->info("Ningún User tiene el rol 'cliente'. Nada que hacer.");

            return;
        }

        $rol->users()->detach();

        Log::info("Rol 'cliente' revocado de {$count} Users (migración portal a Cliente Authenticatable).");
        $this->command?->info("Rol 'cliente' revocado de {$count} Users.");
    }
}
