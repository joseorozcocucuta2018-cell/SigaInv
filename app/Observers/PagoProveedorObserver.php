<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\PagoProveedor;
use App\Services\BancosService;
use App\Services\CajaService;
use App\Services\PagoDistribucionService;
use Illuminate\Support\Facades\Log;

/**
 * Observer para PagoProveedor.
 *
 * La creación se maneja exclusivamente desde PagoProveedorService::crear()
 * para garantizar atomicidad (validación → pago → distribución → movimiento).
 *
 * Este observer solo maneja la reversión al eliminar un pago.
 */
class PagoProveedorObserver
{
    public function __construct(
        protected PagoDistribucionService $distribucionService,
    ) {}

    public function deleting(PagoProveedor $pago): void
    {
        $this->distribucionService->revertirPagoProveedor($pago);

        $this->revertirMovimientoFinanciero($pago);
    }

    private function revertirMovimientoFinanciero(PagoProveedor $pago): void
    {
        $concepto = "Reversión pago proveedor #{$pago->numero} — {$pago->proveedor->nombre}";

        try {
            if ($pago->caja_id) {
                CajaService::registrarIngreso(
                    cajaId: $pago->caja_id,
                    monto: (float) $pago->monto,
                    concepto: $concepto,
                    categoria: 'reversion_pago_proveedor',
                    referencia: $pago->referencia,
                    formaPagoId: $pago->forma_pago_id,
                    documentoTipo: 'pago_proveedor',
                    documentoId: $pago->id,
                );
            }

            if ($pago->banco_id) {
                BancosService::registrarDeposito(
                    bancoId: $pago->banco_id,
                    monto: (float) $pago->monto,
                    concepto: $concepto,
                    categoria: 'reversion_pago_proveedor',
                    referencia: $pago->referencia,
                    formaPagoId: $pago->forma_pago_id,
                    documentoTipo: 'pago_proveedor',
                    documentoId: $pago->id,
                );
            }
        } catch (\Throwable $e) {
            Log::error("Error al revertir movimiento financiero del pago proveedor #{$pago->id}: {$e->getMessage()}");
            throw $e;
        }
    }
}
