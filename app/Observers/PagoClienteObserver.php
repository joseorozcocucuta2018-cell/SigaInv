<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\PagoCliente;
use App\Services\BancosService;
use App\Services\CajaService;
use App\Services\PagoDistribucionService;
use Illuminate\Support\Facades\Log;

/**
 * Observer para PagoCliente.
 *
 * La creación se maneja exclusivamente desde PagoClienteService::crear()
 * para garantizar atomicidad (validación → pago → distribución → movimiento).
 *
 * Este observer maneja notificaciones y la reversión al eliminar un pago.
 */
class PagoClienteObserver
{
    public function __construct(
        protected PagoDistribucionService $distribucionService,
    ) {}

    public function created(PagoCliente $pago): void
    {
        $this->distribucionService->distribuirPagoCliente($pago);
    }

    public function deleting(PagoCliente $pago): void
    {
        $this->distribucionService->revertirPagoCliente($pago);

        $this->revertirMovimientoFinanciero($pago);
    }

    private function revertirMovimientoFinanciero(PagoCliente $pago): void
    {
        $concepto = "Reversión pago cliente #{$pago->numero} — {$pago->cliente->nombre}";

        try {
            if ($pago->caja_id) {
                CajaService::registrarEgreso(
                    cajaId: $pago->caja_id,
                    monto: (float) $pago->monto,
                    concepto: $concepto,
                    categoria: 'reversion_pago_cliente',
                    referencia: $pago->referencia,
                    formaPagoId: $pago->forma_pago_id,
                    documentoTipo: 'pago_cliente',
                    documentoId: $pago->id,
                );
            }

            if ($pago->banco_id) {
                BancosService::registrarRetiro(
                    bancoId: $pago->banco_id,
                    monto: (float) $pago->monto,
                    concepto: $concepto,
                    categoria: 'reversion_pago_cliente',
                    referencia: $pago->referencia,
                    formaPagoId: $pago->forma_pago_id,
                    documentoTipo: 'pago_cliente',
                    documentoId: $pago->id,
                );
            }
        } catch (\Throwable $e) {
            Log::error("Error al revertir movimiento financiero del pago cliente #{$pago->id}: {$e->getMessage()}");
            throw $e;
        }
    }
}
