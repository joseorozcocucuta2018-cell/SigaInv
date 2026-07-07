<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\DevolucionEstado;
use App\Models\Devolucion;
use App\Models\MovimientoInventario;
use App\Models\MovimientoSaldoCliente;
use App\Models\Remision;
use App\Models\StockBodega;
use App\Models\User;
use App\Models\Venta;
use App\Notifications\DevolucionConfirmadaNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use InvalidArgumentException;

class DevolucionService
{
    /**
     * Procesa la confirmación: restaura stock, ajusta saldo, crea movimientos.
     * NO actualiza el estado (lo hace el caller).
     */
    public function procesarConfirmacion(Devolucion $devolucion): void
    {
        if ($devolucion->detalles->isEmpty()) {
            throw new InvalidArgumentException('La devolución no tiene detalles para procesar.');
        }

        $cliente = $devolucion->cliente;

        if (! $cliente) {
            throw new InvalidArgumentException("No se encontró el cliente asociado a la devolución {$devolucion->numero}.");
        }

        $saldoAntes = (float) $cliente->saldo;
        $bodegaId = $this->getBodegaDeDocumento($devolucion);

        foreach ($devolucion->detalles as $detalle) {
            $stock = StockBodega::where('producto_id', $detalle->producto_id)
                ->where('bodega_id', $bodegaId)
                ->first();

            $cantidadAntes = $stock ? (float) $stock->cantidad : 0.0;
            $cantidadDespues = $cantidadAntes + $detalle->cantidad;

            if ($stock) {
                $stock->increment('cantidad', $detalle->cantidad);
            } else {
                StockBodega::create([
                    'producto_id' => $detalle->producto_id,
                    'bodega_id' => $bodegaId,
                    'cantidad' => $detalle->cantidad,
                ]);
            }

            MovimientoInventario::create([
                'producto_id' => $detalle->producto_id,
                'bodega_id' => $bodegaId,
                'tipo_movimiento' => 'entrada_devolucion',
                'cantidad' => $detalle->cantidad,
                'costo_unitario' => 0,
                'stock_resultante' => $cantidadDespues,
                'documento_tipo' => 'devolucion',
                'documento_id' => $devolucion->id,
                'observacion' => "Devolución {$devolucion->numero} - {$devolucion->motivo->value}",
                'usuario_id' => Auth::id() ?? $devolucion->usuario_id,
            ]);
        }

        $nuevoSaldo = $saldoAntes - (float) $devolucion->total;
        $cliente->update(['saldo' => $nuevoSaldo]);

        MovimientoSaldoCliente::create([
            'cliente_id' => $cliente->id,
            'tipo' => 'devolucion',
            'referencia' => "devolucion_{$devolucion->id}",
            'monto' => -(float) $devolucion->total,
            'saldo_anterior' => $saldoAntes,
            'saldo_nuevo' => $nuevoSaldo,
            'descripcion' => "Devolución {$devolucion->numero} - {$devolucion->motivo->value}",
            'usuario_id' => Auth::id() ?? $devolucion->usuario_id,
        ]);
    }

    /**
     * Confirma una devolución desde la UI: procesa y actualiza estado.
     */
    public function confirmar(Devolucion $devolucion): void
    {
        if ($devolucion->estado !== DevolucionEstado::BORRADOR) {
            throw new InvalidArgumentException(
                "Solo se pueden confirmar devoluciones en estado borrador. Estado actual: {$devolucion->estado->value}"
            );
        }

        DB::transaction(function () use ($devolucion) {
            $this->procesarConfirmacion($devolucion);

            $devolucion->skipProcessing = true;
            $devolucion->update([
                'estado' => DevolucionEstado::CONFIRMADA,
                'confirmada_en' => now(),
            ]);
        });

        $this->notificarConfirmacion($devolucion);
    }

    /**
     * Anula una devolución confirmada, revirtiendo stock y saldo del cliente.
     */
    public function anular(Devolucion $devolucion, ?string $razon = null): void
    {
        if ($devolucion->estado !== DevolucionEstado::CONFIRMADA) {
            throw new InvalidArgumentException(
                "Solo se pueden anular devoluciones en estado confirmada. Estado actual: {$devolucion->estado->value}"
            );
        }

        DB::transaction(function () use ($devolucion, $razon) {
            $cliente = $devolucion->cliente;

            if (! $cliente) {
                throw new InvalidArgumentException("No se encontró el cliente asociado a la devolución {$devolucion->numero}.");
            }

            $saldoAntes = (float) $cliente->saldo;
            $bodegaId = $this->getBodegaDeDocumento($devolucion);

            foreach ($devolucion->detalles as $detalle) {
                $stock = StockBodega::where('producto_id', $detalle->producto_id)
                    ->where('bodega_id', $bodegaId)
                    ->first();

                $cantidadAntes = $stock ? (float) $stock->cantidad : 0.0;
                $cantidadDespues = $cantidadAntes - $detalle->cantidad;

                if ($stock) {
                    $stock->decrement('cantidad', $detalle->cantidad);
                }

                MovimientoInventario::create([
                    'producto_id' => $detalle->producto_id,
                    'bodega_id' => $bodegaId,
                    'tipo_movimiento' => 'salida_anulacion_devolucion',
                    'cantidad' => $detalle->cantidad,
                    'costo_unitario' => 0,
                    'stock_resultante' => $cantidadDespues,
                    'documento_tipo' => 'devolucion',
                    'documento_id' => $devolucion->id,
                    'observacion' => "Anulación devolución {$devolucion->numero}".($razon ? " - {$razon}" : ''),
                    'usuario_id' => Auth::id() ?? $devolucion->usuario_id,
                    'fecha_movimiento' => now(),
                ]);
            }

            $nuevoSaldo = $saldoAntes + (float) $devolucion->total;
            $cliente->update(['saldo' => $nuevoSaldo]);

            MovimientoSaldoCliente::create([
                'cliente_id' => $cliente->id,
                'tipo' => 'anulacion_devolucion',
                'referencia' => "anulacion_devolucion_{$devolucion->id}",
                'monto' => (float) $devolucion->total,
                'saldo_anterior' => $saldoAntes,
                'saldo_nuevo' => $nuevoSaldo,
                'descripcion' => "Anulación devolución {$devolucion->numero}".($razon ? " - {$razon}" : ''),
                'usuario_id' => Auth::id() ?? $devolucion->usuario_id,
            ]);

            $devolucion->update([
                'estado' => DevolucionEstado::ANULADA,
                'observaciones' => trim(($devolucion->observaciones ?? '')."\nAnulada: ".($razon ?? 'Sin motivo especificado')),
            ]);
        });
    }

    /**
     * Obtiene la bodega del documento origen (venta o remisión).
     */
    private function getBodegaDeDocumento(Devolucion $devolucion): int
    {
        if ($devolucion->tipo_documento === 'remision') {
            $bodegaId = Remision::find($devolucion->documento_id)?->bodega_id;
        } else {
            $bodegaId = Venta::find($devolucion->documento_id)?->bodega_id;
        }

        if (! $bodegaId) {
            throw new InvalidArgumentException(
                "No se encontró el documento {$devolucion->tipo_documento} #{$devolucion->documento_id} para obtener la bodega."
            );
        }

        return $bodegaId;
    }

    /**
     * Notifica la confirmación de devolución a admins y cliente.
     */
    private function notificarConfirmacion(Devolucion $devolucion): void
    {
        try {
            $admins = User::role(['administrador'])->get();
            if ($admins->isNotEmpty()) {
                Notification::send($admins, new DevolucionConfirmadaNotification($devolucion));
            }

            $cliente = $devolucion->cliente;
            if ($cliente && $cliente->email) {
                Notification::route('mail', $cliente->email)
                    ->notify(new DevolucionConfirmadaNotification($devolucion));
            }
        } catch (\Throwable $e) {
            Log::warning("Error al notificar devolución confirmada: {$e->getMessage()}");
        }
    }
}
