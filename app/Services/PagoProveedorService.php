<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Banco;
use App\Models\Caja;
use App\Models\PagoProveedor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PagoProveedorService
{
    /**
     * Registra un pago a proveedor de forma atómica:
     * - Valida saldo suficiente en caja/banco
     * - Crea el comprobante de egreso
     * - Distribuye el monto en cascada (waterfall) a compras pendientes
     * - Genera el movimiento financiero (egreso caja o retiro banco)
     *
     * Si algo falla, nada se persiste.
     */
    public static function crear(array $data): PagoProveedor
    {
        return DB::transaction(function () use ($data) {
            // 1. Validar saldo suficiente antes de crear
            static::validarSaldo($data);

            // 2. Crear el pago (sin observers — el Service maneja todo)
            $data['usuario_id'] = Auth::id();
            $data['numero'] = static::generarNumero();
            $data['forma_pago_id'] = ! empty($data['banco_id']) ? 2 : 1; // 1=Efectivo, 2=Transferencia
            $pago = PagoProveedor::withoutEvents(fn () => PagoProveedor::create($data));

            // 3. Distribuir en cascada a compras pendientes
            app(PagoDistribucionService::class)->distribuirPagoProveedor($pago);

            // 4. Generar movimiento financiero
            static::crearMovimientoFinanciero($pago);

            return $pago;
        });
    }

    private static function validarSaldo(array $data): void
    {
        $monto = (float) $data['monto'];

        if (! empty($data['caja_id'])) {
            $caja = Caja::lockForUpdate()->findOrFail($data['caja_id']);
            if ($monto > $caja->saldo_actual) {
                throw new \InvalidArgumentException(
                    "Saldo insuficiente en caja «{$caja->nombre}». Saldo actual: \$".number_format($caja->saldo_actual, 0, ',', '.')
                );
            }
        }

        if (! empty($data['banco_id'])) {
            $banco = Banco::lockForUpdate()->findOrFail($data['banco_id']);
            if ($monto > $banco->saldo_actual) {
                throw new \InvalidArgumentException(
                    "Saldo insuficiente en cuenta «{$banco->nombre_banco}». Saldo actual: \$".number_format($banco->saldo_actual, 0, ',', '.')
                );
            }
        }
    }

    private static function crearMovimientoFinanciero(PagoProveedor $pago): void
    {
        $concepto = "Pago a proveedor #{$pago->numero} — {$pago->proveedor->nombre}";

        if ($pago->caja_id) {
            CajaService::registrarEgreso(
                cajaId: $pago->caja_id,
                monto: (float) $pago->monto,
                concepto: $concepto,
                categoria: 'pago_proveedor',
                referencia: $pago->referencia,
                formaPagoId: $pago->forma_pago_id,
                documentoTipo: 'pago_proveedor',
                documentoId: $pago->id,
            );
        }

        if ($pago->banco_id) {
            BancosService::registrarRetiro(
                bancoId: $pago->banco_id,
                monto: (float) $pago->monto,
                concepto: $concepto,
                categoria: 'pago_proveedor',
                referencia: $pago->referencia,
                formaPagoId: $pago->forma_pago_id,
                documentoTipo: 'pago_proveedor',
                documentoId: $pago->id,
            );
        }
    }

    private static function generarNumero(): string
    {
        $last = PagoProveedor::orderBy('id', 'desc')->lockForUpdate()->first();
        $siguiente = $last ? ((int) substr($last->numero, 4)) + 1 : 1;

        return 'PAG-'.str_pad((string) $siguiente, 5, '0', STR_PAD_LEFT);
    }
}
