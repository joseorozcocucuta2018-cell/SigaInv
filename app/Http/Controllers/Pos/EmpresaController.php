<?php

declare(strict_types=1);

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use Illuminate\Http\JsonResponse;

class EmpresaController extends Controller
{
    public function show(): JsonResponse
    {
        $empresa = Empresa::actual();
        if (! $empresa) {
            return response()->json(['data' => null]);
        }

        return response()->json([
            'data' => [
                'id' => (int) $empresa->id,
                'razon_social' => (string) $empresa->razon_social,
                'nombre_comercial' => (string) $empresa->nombre_comercial,
                'nit' => (string) $empresa->nit,
                'direccion' => $empresa->direccion,
                'telefono' => $empresa->telefono,
                'logo' => $empresa->logo,
                'logo_url' => $empresa->logo ? asset('storage/'.$empresa->logo) : null,
                'logo_pos' => $empresa->logo_pos,
                'logo_pos_url' => $empresa->logo_pos ? asset('storage/'.$empresa->logo_pos) : null,
            ],
        ]);
    }
}
