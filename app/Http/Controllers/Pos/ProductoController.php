<?php

declare(strict_types=1);

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductoResource;
use App\Models\Producto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        $limit = (int) $request->query('limit', 50);
        $limit = max(1, min($limit, 200));

        $query = Producto::query()
            ->where('activo', true)
            ->with(['impuesto', 'unidadMedida']);

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('nombre', 'like', "%{$q}%")
                    ->orWhere('codigo', 'like', "%{$q}%")
                    ->orWhere('codigo_barras', 'like', "%{$q}%")
                    ->orWhere('nombre_comun', 'like', "%{$q}%");
            });
        }

        $productos = $query->orderBy('nombre')->limit($limit)->get();

        return response()->json([
            'data' => ProductoResource::collection($productos)->resolve(),
        ]);
    }

    public function show(Producto $producto): JsonResponse
    {
        abort_unless($producto->activo, 404);
        $producto->load(['impuesto', 'unidadMedida']);

        return response()->json([
            'data' => (new ProductoResource($producto))->resolve(),
        ]);
    }
}
