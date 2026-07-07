<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Producto;

class PrecioService
{
    public function calcularPrecioConDescuento(Producto $producto, ?Cliente $cliente = null): float
    {
        if (! $cliente) {
            return (float) ($producto->precio_venta ?? 0);
        }

        return (float) $cliente->aplicarDescuento((float) $producto->precio_venta);
    }

    /**
     * Calcula el precio de venta sugerido a partir del costo, aplicando el margen.
     * Si se especifica un margen, se acota entre el mínimo y máximo (default) de la empresa.
     * Si no se especifica, se usa el default de la empresa.
     */
    public function calcularPrecioVentaSugerido(float $precioCompra, ?float $margenPorcentaje = null): float
    {
        $margen = $this->resolverMargen($margenPorcentaje);

        if ($margen <= 0 || $precioCompra <= 0) {
            return $precioCompra;
        }

        return round($precioCompra / (1 - ($margen / 100)), 2);
    }

    /**
     * Calcula el precio con margen aplicado (markup directo).
     * Misma lógica de acotación que calcularPrecioVentaSugerido.
     */
    public function calcularPrecioConMargen(float $precioCompra, ?float $margenPorcentaje = null): float
    {
        $margen = $this->resolverMargen($margenPorcentaje);

        if ($margen <= 0 || $precioCompra <= 0) {
            return $precioCompra;
        }

        return round($precioCompra * (1 + $margen / 100), -1);
    }

    /**
     * Resuelve el margen a aplicar, acotándolo entre el mínimo y el máximo (default)
     * configurados en la empresa. Si no se especifica margen, se usa el default.
     */
    private function resolverMargen(?float $margenManual): float
    {
        $empresa = Empresa::first();

        $minimo = (float) ($empresa?->margen_ganancia_minimo ?? 0);
        $maximo = (float) ($empresa?->margen_ganancia_default ?? 30);

        if ($margenManual === null) {
            return $maximo;
        }

        return max($minimo, min($margenManual, $maximo));
    }
}
