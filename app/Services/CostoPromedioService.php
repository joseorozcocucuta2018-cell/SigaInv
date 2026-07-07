<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\MovimientoInventario;
use App\Models\Producto;

/**
 * Servicio para calcular el costo promedio ponderado de productos
 *
 * Fórmula: ((stock_actual * costo_actual) + (cantidad_nueva * costo_nueva)) / (stock_actual + cantidad_nueva)
 */
class CostoPromedioService
{
    /**
     * Calcula y actualiza el costo promedio de un producto
     * Se llama al confirmar una compra
     *
     * @param  int  $productoId  ID del producto
     * @param  float  $stockAntes  Stock del producto ANTES de la operación (snapshot)
     * @param  float  $cantidadNueva  Cantidad de la nueva entrada
     * @param  float  $costoNuevo  Costo unitario de la nueva entrada
     * @return float El nuevo costo promedio
     */
    public static function calcularCostoPromedio(int $productoId, float $stockAntes, float $cantidadNueva, float $costoNuevo): float
    {
        $producto = Producto::findOrFail($productoId);

        // Obtener costo actual (costo_promedio o precio_compra como fallback)
        $costoActual = (float) ($producto->costo_promedio ?? $producto->precio_compra);

        // Calcular subtotales
        $subTotal = ($stockAntes * $costoActual) + ($cantidadNueva * $costoNuevo);
        $subCantidad = $stockAntes + $cantidadNueva;

        // Calcular CPP: subTotal / subCantidad (o costoNuevo si no hay stock)
        $nuevoCosto = $subCantidad > 0 ? round($subTotal / $subCantidad, 4) : $costoNuevo;

        // Actualizar el producto
        $producto->update([
            'costo_promedio' => $nuevoCosto,
            'precio_compra' => $costoNuevo, // Actualizar precio_compra al último costo
        ]);

        // Registrar en movimiento_inventario el costo_real_aplicado
        self::registrarCostoEnMovimiento($productoId, $nuevoCosto);

        return $nuevoCosto;
    }

    /**
     * Registra el costo promedio en el último movimiento de inventario del producto
     * Esto permite trazabilidad del costo aplicado
     */
    private static function registrarCostoEnMovimiento(int $productoId, float $costo): void
    {
        // Buscar el último movimiento de entrada (compra) sin costo registrado
        $ultimoMovimiento = MovimientoInventario::where('producto_id', $productoId)
            ->whereIn('tipo_movimiento', ['entrada_compra', 'compra'])
            ->orderBy('id', 'desc')
            ->first();

        if ($ultimoMovimiento && $ultimoMovimiento->costo_unitario != $costo) {
            // Registrar un movimiento de ajuste de costo
            MovimientoInventario::create([
                'producto_id' => $productoId,
                'bodega_id' => $ultimoMovimiento->bodega_id,
                'tipo_movimiento' => 'ajuste_costo_promedio',
                'cantidad' => 0,
                'costo_unitario' => $costo,
                'stock_resultante' => $ultimoMovimiento->stock_resultante,
                'documento_tipo' => $ultimoMovimiento->documento_tipo,
                'documento_id' => $ultimoMovimiento->documento_id,
                'observacion' => 'Ajuste automático de costo promedio ponderado',
                'fecha_movimiento' => now(),
            ]);
        }
    }

    /**
     * Revierte el cálculo de costo promedio al anular una compra
     * Recalcula basándose en el stock remaining
     *
     * @param  int  $productoId  ID del producto
     * @param  float  $cantidadAnulada  Cantidad que se está anulando
     * @param  float  $costoAnulado  Costo unitario de la compra anulada
     */
    public static function revertirCostoPromedio(int $productoId, float $cantidadAnulada, float $costoAnulado): void
    {
        $producto = Producto::findOrFail($productoId);

        // Obtener stock actual DESPUÉS de revertir la compra
        $stockActual = StockService::getTotalStockForProduct($productoId);

        if ($stockActual <= 0) {
            // Si no queda stock, el costo promedio queda en null o en el último costo conocido
            $producto->update(['costo_promedio' => null]);
        } else {
            // Recalcular el costo promedio restante
            // El costo actual del producto representa el promedio hasta antes de la reversión
            $costoActual = (float) ($producto->costo_promedio ?? $producto->precio_compra);

            // Inversa de la fórmula: nuevo_promedio = (costo_promedio * stock_total - cantidad_anulada * costo_anulado) / stock_actual
            $stockTotalConAnulacion = $stockActual + $cantidadAnulada;
            $valorTotalAnterior = $stockTotalConAnulacion * $costoActual;
            $valorEliminado = $cantidadAnulada * $costoAnulado;
            $valorTotalActual = $valorTotalAnterior - $valorEliminado;

            $nuevoCosto = $stockActual > 0 ? $valorTotalActual / $stockActual : 0;
            $nuevoCosto = round(max(0, $nuevoCosto), 4);

            $producto->update(['costo_promedio' => $nuevoCosto]);
        }
    }
}
