<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\DetalleRemision;
use App\Services\StockService;
use InvalidArgumentException;

class DetalleRemisionObserver
{
    /**
     * Cuando se crea un detalle de remisión:
     * ⚠️ NOTA: No se generan movimientos aquí.
     *    Los movimientos se crean cuando la remisión es CONFIRMADA.
     *    Esto asegura idempotencia y permite editar documentos en borrador.
     */
    public function created(DetalleRemision $detalle): void
    {
        // Validación básica: cantidad positiva
        StockService::validateQuantity($detalle->cantidad);
    }

    /**
     * Cuando se elimina un detalle de remisión:
     * ⚠️ Solo se permite eliminar detalles si la remisión está en estado BORRADOR.
     *    Si está confirmada, debe anularse la remisión completa.
     */
    public function deleted(DetalleRemision $detalle): void
    {
        $remision = $detalle->remision;
        if (! $remision) {
            return;
        }

        // Si el documento está confirmado, no permitir eliminación de detalles
        if ($remision->estado && ! $remision->estado->isEditable()) {
            throw new InvalidArgumentException(
                "No se pueden eliminar detalles de una remisión {$remision->estado->label()}. ".
                'Anule la remisión completa si es necesario.'
            );
        }

        // Si está en borrador, no registrar movimiento (aún no hay stock afectado)
        // La eliminación de detalles en borrador es simple limpieza
    }
}
