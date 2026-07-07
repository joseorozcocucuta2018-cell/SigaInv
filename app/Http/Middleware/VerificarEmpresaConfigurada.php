<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Empresa;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class VerificarEmpresaConfigurada
{
    public function handle(Request $request, Closure $next): Response
    {

        $user = Auth::user();

        if (! $user) {
            return $next($request);
        }

        // Si la empresa ya está configurada, continuar normal
        if (Empresa::count() > 0) {
            return $next($request);
        }

        $path = $request->path();

        // Excluir rutas de autenticación, MFA y la propia página de crear empresa
        // 'auth/' cubre: login, MFA, recuperación de contraseña, verificación email
        if (
            str_contains($path, 'auth/') ||
            str_contains($path, 'multi-factor-authentication') ||
            str_contains($path, 'empresas/create') ||
            str_contains($path, 'livewire/') ||
            str_contains($path, 'logout')
        ) {
            return $next($request);
        }

        // Generar URL de creación de empresa
        $urlCrearEmpresa = Filament::getPanel('admin')->getUrl().'/empresas/create';

        // Administrador → redirigir a crear empresa
        if ($user->hasRole('administrador')) {
            return redirect($urlCrearEmpresa)
                ->with('filament.notifications', [[
                    'id' => uniqid(),
                    'type' => 'warning',
                    'title' => 'Empresa no configurada',
                    'body' => 'Por favor complete los datos de la empresa antes de continuar.',
                    'duration' => 'persistent',
                ]]);
        }

        // Otros roles → pantalla en construcción
        return response()->view('filament.empresa-no-configurada', [
            'mensaje' => 'El sistema está siendo configurado por el administrador. Por favor intente más tarde.',
        ], 503);
    }
}
