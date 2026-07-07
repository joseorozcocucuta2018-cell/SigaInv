<?php

declare(strict_types=1);

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pos\PosCrearRemisionRequest;
use App\Http\Resources\RemisionResource;
use App\Models\Remision;
use App\Services\RemisionService;
use Exception;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class RemisionController extends Controller
{
    public function store(PosCrearRemisionRequest $request): JsonResponse
    {
        try {
            $resultado = RemisionService::crearPos(
                auth()->user(),
                $request->validated(),
                $request->validated()['items'] ?? [],
            );
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        $remision = Remision::find($resultado['id']);

        return response()->json([
            'id' => $resultado['id'],
            'numero' => $resultado['numero'],
            'total' => $resultado['total'],
            'pagos' => $resultado['pagos'],
            'data' => (new RemisionResource($remision))->resolve(),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $remision = Remision::find($id);
        if (! $remision) {
            return response()->json(['error' => 'Remisión no encontrada.'], 404);
        }

        return response()->json([
            'data' => (new RemisionResource($remision))->resolve(),
        ]);
    }
}
