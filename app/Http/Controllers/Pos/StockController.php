<?php

declare(strict_types=1);

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\StockBodega;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $bodegaId = (int) $request->query('bodega_id', 0);
        $productoId = (int) $request->query('producto_id', 0);

        $query = StockBodega::query();
        if ($bodegaId > 0) {
            $query->where('bodega_id', $bodegaId);
        }
        if ($productoId > 0) {
            $query->where('producto_id', $productoId);
        }

        $stocks = $query->get()->map(fn ($s) => [
            'producto_id' => (int) $s->producto_id,
            'bodega_id' => (int) $s->bodega_id,
            'cantidad' => (float) $s->cantidad,
        ])->values();

        return response()->json(['data' => $stocks]);
    }
}
