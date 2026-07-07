<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Empresa;
use App\Models\Producto;
use Illuminate\Support\Facades\Log;

class CostoService
{
    /**
     * Resuelve el costo unitario de un producto priorizando costo_promedio
     * con fallback a precio_compra
     */
    public static function resolveCostoUnitario(?Producto $producto): float
    {
        if (! $producto) {
            return 0;
        }

        return (float) ($producto->costo_promedio ?: $producto->precio_compra ?? 0);
    }

    /**
     * Calcula los costos de una fórmula de transformación.
     *
     * @return array{total_costo: float, precio_sugerido: float, margen_aplicado: float}
     */
    public static function calcularCostos(array $detalles, ?float $margenManual = null, ?string $tipoCalculo = null, ?float $precioManual = null): array
    {
        Log::info('CostoService Debug:', ['detalles' => $detalles]);

        $total = 0;
        $ids = array_filter(array_column($detalles, 'producto_id'));
        $productos = Producto::whereIn('id', $ids)->get()->keyBy('id');

        Log::info('CostoService Debug Productos:', ['ids' => $ids, 'count' => $productos->count()]);

        $empresa = Empresa::actual();
        $defaultMargen = $empresa ? (float) $empresa->margen_ganancia_default : 30.0;

        foreach ($detalles as $detalle) {
            $productoId = $detalle['producto_id'] ?? null;
            if ($productoId && isset($productos[$productoId])) {
                $producto = $productos[$productoId];
                $costoUnitario = (float) ($producto->costo_promedio > 0 ? $producto->costo_promedio : ($producto->precio_compra ?? 0));
                $cantidad = (float) ($detalle['cantidad'] ?? 0);
                $total += $costoUnitario * $cantidad;
                Log::info('CostoService Debug Loop:', ['id' => $productoId, 'costo' => $costoUnitario, 'cantidad' => $cantidad, 'total' => $total]);
            }
        }

        if ($tipoCalculo === 'manual' && $precioManual !== null && $precioManual > 0) {
            $precio = $precioManual;
            $margenCalculado = $precio > 0 ? (($precio - $total) / $precio) * 100 : 0;
        } else {
            $margenFinal = $margenManual ?? $defaultMargen;
            $precio = $margenFinal < 100 ? ($total / (1 - ($margenFinal / 100))) : $total;
            $margenCalculado = $margenFinal;
        }

        return [
            'total_costo' => $total,
            'margen_aplicado' => round($margenCalculado, 2),
            'precio_sugerido' => $precio,
        ];
    }
}
