<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\UserEstado;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Asegura que el usuario autenticado pueda usar el POS.
 *
 *  - El usuario debe estar autenticado (auth:sanctum antes de este middleware).
 *  - El estado del usuario debe ser ACTIVO.
 *  - El usuario debe tener al menos uno de los roles configurados en
 *    config/pos.php (roles_autorizados).
 *
 * Devuelve 401 si no hay sesión, 403 si está inactivo o no tiene rol.
 */
class EnsurePosAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        if (method_exists($user, 'getAttribute') && $user->getAttribute('estado') !== UserEstado::ACTIVO) {
            return response()->json(['error' => 'Usuario inactivo.'], 403);
        }

        $roles = (array) config('pos.roles_autorizados', ['vendedor', 'administrador']);

        if (! method_exists($user, 'hasAnyRole')) {
            return response()->json(['error' => 'Sin permisos para acceder al POS.'], 403);
        }

        if (! $user->hasAnyRole($roles)) {
            return response()->json(['error' => 'No tienes un rol autorizado para el POS.'], 403);
        }

        return $next($request);
    }
}
