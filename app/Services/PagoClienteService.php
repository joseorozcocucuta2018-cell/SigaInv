<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PagoCliente;
use App\Notifications\PagoRecibidoNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class PagoClienteService
{
    /**
     * Registra un pago de cliente de forma atómica:
     * - Valida saldo suficiente si es egreso (poco común en pago cliente)
     * - Crea el recibo de pago
     * - Distribuye el monto en cascada (waterfall) a documentos pendientes
     * - Genera el movimiento financiero (ingreso caja o depósito banco)
     *
     * Si algo falla, nada se persiste.
     */
    public static function crear(array $data): PagoCliente
    {
        $pago = DB::transaction(function () use ($data) {
            $data['usuario_id'] = Auth::id();
            $data['numero'] = $data['numero'] ?? static::generarNumero();
            $pago = PagoCliente::withoutEvents(fn () => PagoCliente::create($data));

            app(PagoDistribucionService::class)->distribuirPagoCliente($pago);

            static::crearMovimientoFinanciero($pago);

            return $pago;
        });

        static::notificar($pago);

        return $pago;
    }

    private static function notificar(PagoCliente $pago): void
    {
        try {
            $cliente = $pago->cliente;
            if ($cliente && $cliente->email) {
                Notification::route('mail', $cliente->email)
                    ->notify(new PagoRecibidoNotification($pago));
            }
        } catch (\Throwable $e) {
            Log::warning("Error al enviar notificación de pago recibido: {$e->getMessage()}");
        }
    }

    private static function crearMovimientoFinanciero(PagoCliente $pago): void
    {
        $concepto = "Pago recibido #{$pago->numero} — {$pago->cliente->nombre}";

        if ($pago->caja_id) {
            CajaService::registrarIngreso(
                cajaId: $pago->caja_id,
                monto: (float) $pago->monto,
                concepto: $concepto,
                categoria: 'pago_cliente',
                referencia: $pago->referencia,
                formaPagoId: $pago->forma_pago_id,
                documentoTipo: 'pago_cliente',
                documentoId: $pago->id,
            );
        }

        if ($pago->banco_id) {
            BancosService::registrarDeposito(
                bancoId: $pago->banco_id,
                monto: (float) $pago->monto,
                concepto: $concepto,
                categoria: 'pago_cliente',
                referencia: $pago->referencia,
                formaPagoId: $pago->forma_pago_id,
                documentoTipo: 'pago_cliente',
                documentoId: $pago->id,
            );
        }
    }

    private static function generarNumero(): string
    {
        $last = PagoCliente::orderBy('id', 'desc')->lockForUpdate()->first();
        $siguiente = $last ? ((int) substr($last->numero, 3)) + 1 : 1;

        return 'RC-'.str_pad((string) $siguiente, 5, '0', STR_PAD_LEFT);
    }
}
