<?php

declare(strict_types=1);

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pos\PosCrearVentaRequest;
use App\Http\Resources\VentaResource;
use App\Models\Venta;
use App\Services\VentaService;
use Exception;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class VentaController extends Controller
{
    public function store(PosCrearVentaRequest $request): JsonResponse
    {
        try {
            $resultado = VentaService::crearPos(
                auth()->user(),
                $request->validated(),
                $request->validated()['items'] ?? [],
            );
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        $venta = Venta::find($resultado['id']);

        return response()->json([
            'id' => $resultado['id'],
            'numero' => $resultado['numero'],
            'total' => $resultado['total'],
            'pagos' => $resultado['pagos'],
            'data' => (new VentaResource($venta))->resolve(),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $venta = Venta::find($id);
        if (! $venta) {
            return response()->json(['error' => 'Venta no encontrada.'], 404);
        }

        return response()->json([
            'data' => (new VentaResource($venta))->resolve(),
        ]);
    }
}
