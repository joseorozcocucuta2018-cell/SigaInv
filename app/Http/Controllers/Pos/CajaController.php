<?php

declare(strict_types=1);

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Caja;
use App\Services\TurnoService;
use Illuminate\Http\JsonResponse;

class CajaController extends Controller
{
    public function index(): JsonResponse
    {
        $cajas = Caja::where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'estado'])
            ->map(fn ($c) => [
                'id' => (int) $c->id,
                'nombre' => (string) $c->nombre,
                'estado' => (string) $c->estado,
            ]);

        return response()->json(['data' => $cajas]);
    }

    public function show(Caja $caja): JsonResponse
    {
        $turno = TurnoService::getActivoByCaja($caja);
        $resumen = $turno ? TurnoService::resumen($turno) : null;

        return response()->json([
            'data' => [
                'id' => (int) $caja->id,
                'nombre' => (string) $caja->nombre,
                'estado' => (string) $caja->estado,
                'turno_activo' => $turno ? [
                    'id' => (int) $turno->id,
                    'usuario_id' => (int) $turno->usuario_id,
                    'saldo_inicial' => (float) $turno->saldo_inicial,
                    'fecha_apertura' => optional($turno->fecha_apertura)->toIso8601String(),
                ] : null,
                'resumen' => $resumen,
            ],
        ]);
    }
}
