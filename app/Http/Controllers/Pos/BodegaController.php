<?php

declare(strict_types=1);

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Bodega;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BodegaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $bodegas = Bodega::where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'direccion1'])
            ->map(fn ($b) => [
                'id' => (int) $b->id,
                'nombre' => (string) $b->nombre,
                'direccion' => $b->direccion1,
            ]);

        return response()->json(['data' => $bodegas]);
    }
}
