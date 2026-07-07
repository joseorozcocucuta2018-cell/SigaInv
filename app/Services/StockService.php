<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Producto;
use App\Models\StockBodega;
use App\Models\StockBodegaLote;
use InvalidArgumentException;

class StockService
{
    /**
     * Valida que exista suficiente stock en una bodega.
     * Si se pasa lote (o el producto exige_lote), buscará en table stock_bodega_lotes.
     *
     * @param  \DateTimeInterface|string|null  $fechaVencimiento
     *
     * @throws InvalidArgumentException Si no hay stock suficiente
     */
    public static function validateStock(int $productoId, int $bodegaId, float $cantidad, ?string $lote = null, $fechaVencimiento = null): bool
    {
        // si se especifica lote o el producto demanda lote, consultamos tabla de lotes
        $producto = Producto::find($productoId);
        $usaLotes = $lote !== null || ($producto && $producto->exige_lote);

        if ($usaLotes) {
            $query = StockBodegaLote::whereHas('stock', function ($q) use ($bodegaId, $productoId) {
                $q->where('bodega_id', $bodegaId)
                    ->where('producto_id', $productoId);
            });
            if ($lote !== null) {
                $query->where('lote', $lote);
            }
            if ($fechaVencimiento !== null) {
                // use whereDate for compatibility with datetime storage
                $query->whereDate('fecha_vencimiento', $fechaVencimiento);
            }
            $stockLote = $query->first();
            $disponible = $stockLote?->cantidad ?? 0;
        } else {
            $stock = StockBodega::where('producto_id', $productoId)
                ->where('bodega_id', $bodegaId)
                ->first();
            $disponible = $stock?->cantidad ?? 0;
        }

        if ($cantidad > $disponible) {
            throw new InvalidArgumentException(
                "Stock insuficiente para el producto ID {$productoId} en bodega ID {$bodegaId}. "
                ."Solicitado: {$cantidad}, Disponible: {$disponible}"
            );
        }

        return true;
    }

    /**
     * Obtiene el stock total de un producto (todas las bodegas, incl. lotes).
     * Usado para recalcular costo promedio ponderado en compras.
     */
    public static function getTotalStockForProduct(int $productoId): float
    {
        $producto = Producto::find($productoId);
        $usaLotes = $producto && $producto->exige_lote;

        if ($usaLotes) {
            $total = StockBodegaLote::whereHas('stock', function ($q) use ($productoId) {
                $q->where('producto_id', $productoId);
            })->sum('cantidad');
        } else {
            $total = StockBodega::where('producto_id', $productoId)->sum('cantidad');
        }

        return round((float) $total, 3);
    }

    /**
     * Obtiene el stock disponible de un producto en una bodega.
     * Puede recibir un lote/fec venc para filtrar.
     *
     * @param  \DateTimeInterface|string|null  $fechaVencimiento
     */
    public static function getAvailableStock(int $productoId, int $bodegaId, ?string $lote = null, $fechaVencimiento = null): float
    {
        if ($lote !== null) {
            $query = StockBodegaLote::whereHas('stock', function ($q) use ($bodegaId, $productoId) {
                $q->where('bodega_id', $bodegaId)
                    ->where('producto_id', $productoId);
            });
            if ($lote !== null) {
                $query->where('lote', $lote);
            }
            if ($fechaVencimiento !== null) {
                // compare only date portion
                $query->whereDate('fecha_vencimiento', $fechaVencimiento);
            }
            $stockLote = $query->first();

            return (float) ($stockLote?->cantidad ?? 0);
        }

        $stock = StockBodega::where('producto_id', $productoId)
            ->where('bodega_id', $bodegaId)
            ->first();

        return (float) ($stock?->cantidad ?? 0);
    }

    /**
     * Valida que una cantidad sea válida (positiva y no cero).
     *
     * @throws InvalidArgumentException
     */
    public static function validateQuantity(float|int|string $cantidad): bool
    {
        $cantidad = (float) $cantidad;

        if ($cantidad <= 0) {
            throw new InvalidArgumentException('La cantidad debe ser mayor a 0');
        }

        return true;
    }
}
