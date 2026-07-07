<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\DetalleCompra;
use App\Services\StockService;
use InvalidArgumentException;

class DetalleCompraObserver
{
    /**
     * Cuando se crea un detalle de compra:
     * ⚠️ NOTA: No se generan movimientos aquí.
     *    Los movimientos se crean cuando la compra es CONFIRMADA.
     *    Esto asegura idempotencia y permite editar documentos en borrador.
     */
    public function created(DetalleCompra $detalle): void
    {
        // Validación básica: cantidad positiva
        StockService::validateQuantity($detalle->cantidad);
    }

    /**
     * Cuando se elimina un detalle de compra:
     * ⚠️ Solo se permite eliminar detalles si la compra está en estado BORRADOR.
     *    Si está confirmada, debe anularse la compra completa.
     */
    public function deleted(DetalleCompra $detalle): void
    {
        $compra = $detalle->compra;
        if (! $compra) {
            return;
        }

        // Si el documento está confirmado, no permitir eliminación de detalles
        if ($compra->estado && ! $compra->estado->isEditable()) {
            throw new InvalidArgumentException(
                "No se pueden eliminar detalles de una compra {$compra->estado->label()}. "
                .'Anule la compra completa si es necesario.'
            );
        }

        // Si está en borrador, no registrar movimiento (aún no hay stock afectado)
        // La eliminación de detalles en borrador es simple limpieza
    }
}
