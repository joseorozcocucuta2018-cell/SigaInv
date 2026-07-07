<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Cliente;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Fuerza la redirección a cambiar-password cuando un cliente autenticado
 * aún tiene la contraseña temporal (password_changed_at = null) generada
 * por el admin.
 *
 * Se excluye la propia página de cambio de contraseña, la página de login
 * y la ruta de logout para evitar bucles de redirección.
 */
class ClienteForzarCambioPassword
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('cliente')->user();

        if (! $user instanceof Cliente) {
            return $next($request);
        }

        if (! $user->debeCambiarPassword()) {
            return $next($request);
        }

        $rutasPermitidas = [
            'filament.clientes.pages.cambiar-password',
            'filament.clientes.auth.login',
            'filament.clientes.auth.logout',
        ];

        if (in_array($request->route()?->getName(), $rutasPermitidas, true)) {
            return $next($request);
        }

        return redirect()->route('filament.clientes.pages.cambiar-password');
    }
}
