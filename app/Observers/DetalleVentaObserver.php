<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\DetalleVenta;
use App\Services\StockService;
use InvalidArgumentException;

class DetalleVentaObserver
{
    /**
     * Cuando se crea un detalle de venta:
     * ⚠️ NOTA: No se generan movimientos aquí.
     *    Los movimientos se crean cuando la venta es CONFIRMADA.
     *    Esto asegura idempotencia y permite editar documentos en borrador.
     */
    public function created(DetalleVenta $detalle): void
    {
        // Validación básica: cantidad positiva
        StockService::validateQuantity($detalle->cantidad);
    }

    /**
     * Cuando se elimina un detalle de venta:
     * ⚠️ Solo se permite eliminar detalles si la venta está en estado BORRADOR.
     *    Si está confirmada, debe anularse la venta completa.
     */
    public function deleted(DetalleVenta $detalle): void
    {
        $venta = $detalle->venta;
        if (! $venta) {
            return;
        }

        // Si el documento está confirmado, no permitir eliminación de detalles
        if ($venta->estado && ! $venta->estado->isEditable()) {
            throw new InvalidArgumentException(
                "No se pueden eliminar detalles de una venta {$venta->estado->label()}. "
                .'Anule la venta completa si es necesario.'
            );
        }

        // Si está en borrador, no registrar movimiento (aún no hay stock afectado)
        // La eliminación de detalles en borrador es simple limpieza
    }
}
