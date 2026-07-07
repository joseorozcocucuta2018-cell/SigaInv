<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PortalAccesoEnum;
use App\Mail\PortalCredencialesMail;
use App\Models\Cliente;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Lógica de gestión de acceso al portal de clientes.
 *
 * Centraliza:
 *  - Generación de contraseña temporal
 *  - Envío de credenciales por email
 *  - Validación de que el cliente puede tener acceso al portal
 */
class PortalAccesoService
{
    /**
     * Genera una contraseña temporal, la guarda hasheada en el cliente
     * y encola un email con las credenciales.
     *
     * Retorna la contraseña en texto plano (por si el caller quiere
     * loggearla o devolverla al admin en algún contexto).
     *
     * @return string|null Contraseña generada, o null si no se pudo
     */
    public function generarYEnviarPassword(Cliente $cliente): ?string
    {
        if (! $this->puedeTenerAcceso($cliente)) {
            return null;
        }

        $passwordPlano = Str::random(12);

        $cliente->forceFill([
            'password' => Hash::make($passwordPlano),
            'password_changed_at' => null,
        ])->save();

        try {
            Mail::to($cliente->email)->queue(
                new PortalCredencialesMail($cliente, $passwordPlano)
            );
        } catch (\Throwable $e) {
            Log::error('No se pudo enviar email de credenciales del portal', [
                'cliente_id' => $cliente->id,
                'email' => $cliente->email,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }

        return $passwordPlano;
    }

    public function puedeTenerAcceso(Cliente $cliente): bool
    {
        return $cliente->portal_acceso?->value === PortalAccesoEnum::ACTIVO->value
            && ! empty($cliente->email)
            && $cliente->email !== 'no_tiene_correo@correo.com';
    }
}
