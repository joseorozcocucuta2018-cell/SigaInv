<?php

declare(strict_types=1);

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pos\PosAbrirTurnoRequest;
use App\Http\Requests\Pos\PosCerrarTurnoRequest;
use App\Http\Resources\TurnoResource;
use App\Models\Turno;
use App\Services\TurnoService;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class TurnoController extends Controller
{
    public function activo(): JsonResponse
    {
        [$turno, $data] = TurnoService::getActivo(auth()->user());
        if (! $turno) {
            return response()->json(['data' => null]);
        }

        return response()->json([
            'data' => array_merge(
                (new TurnoResource($turno))->resolve(),
                ['resumen' => $data],
            ),
        ]);
    }

    public function abrir(PosAbrirTurnoRequest $request): JsonResponse
    {
        try {
            $turno = TurnoService::abrir(auth()->user(), $request->validated());
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        return response()->json([
            'data' => (new TurnoResource($turno))->resolve(),
        ], 201);
    }

    public function cerrar(PosCerrarTurnoRequest $request): JsonResponse
    {
        [$turno] = TurnoService::getActivo(auth()->user());
        if (! $turno) {
            return response()->json(['error' => 'No tienes un turno abierto.'], 404);
        }

        try {
            $cerrado = TurnoService::cerrar(
                auth()->user(),
                (float) $request->input('saldo_final_real'),
                $request->input('observaciones'),
            );
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        return response()->json([
            'data' => $cerrado,
        ]);
    }

    public function resumen(Turno $turno): JsonResponse
    {
        if ($turno->usuario_id !== auth()->id()) {
            return response()->json(['error' => 'No autorizado.'], 403);
        }

        $resumen = TurnoService::resumen($turno);

        return response()->json([
            'data' => array_merge(
                (new TurnoResource($turno))->resolve(),
                ['resumen' => $resumen],
            ),
        ]);
    }
}
