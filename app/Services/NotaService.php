<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\MovimientoInventario;
use App\Models\MovimientoSaldoCliente;
use App\Models\Nota;
use App\Models\StockBodega;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotaService
{
    public function confirmar(Nota $nota): void
    {
        if ($nota->estado !== 'borrador') {
            throw new Exception('La nota ya ha sido procesada.');
        }

        DB::transaction(function () use ($nota) {
            // 1. Ajustar Cartera del Cliente
            $cliente = $nota->cliente;
            $saldoAnterior = $cliente->saldo;

            // Nota Crédito resta saldo, Nota Débito suma saldo
            $montoEfectivo = $nota->tipo === 'nota_credito' ? -$nota->total : $nota->total;
            $cliente->increment('saldo', $montoEfectivo);
            $cliente->refresh();

            MovimientoSaldoCliente::create([
                'cliente_id' => $cliente->id,
                'tipo' => $nota->tipo === 'nota_credito' ? 'devolucion' : 'ajuste',
                'referencia' => $nota->numero,
                'monto' => abs($nota->total),
                'saldo_anterior' => $saldoAnterior,
                'saldo_nuevo' => $cliente->saldo,
                'descripcion' => "{$nota->tipo} #{$nota->numero} - Motivo: {$nota->motivo}",
                'usuario_id' => Auth::id(),
            ]);

            // 2. Ajustar Inventario (si aplica y si hay venta_id)
            // En una nota crédito por devolución, el producto vuelve a la bodega
            if ($nota->tipo === 'nota_credito' && $nota->venta_id) {
                $bodegaId = $nota->venta->bodega_id;

                foreach ($nota->detalles as $detalle) {
                    $stock = StockBodega::where('producto_id', $detalle->producto_id)
                        ->where('bodega_id', $bodegaId)
                        ->lockForUpdate()
                        ->first();

                    if (! $stock) {
                        $stock = StockBodega::create([
                            'producto_id' => $detalle->producto_id,
                            'bodega_id' => $bodegaId,
                            'cantidad' => 0,
                        ]);
                    }

                    $stock->increment('cantidad', $detalle->cantidad);
                    $stock->refresh();

                    MovimientoInventario::create([
                        'producto_id' => $detalle->producto_id,
                        'bodega_id' => $bodegaId,
                        'tipo_movimiento' => 'entrada_devolucion',
                        'cantidad' => $detalle->cantidad,
                        'costo_unitario' => $detalle->producto->costo_promedio,
                        'stock_resultante' => $stock->cantidad,
                        'documento_tipo' => 'nota_credito',
                        'documento_id' => $nota->id,
                        'observacion' => "Entrada por Nota Crédito #{$nota->numero}",
                        'usuario_id' => Auth::id(),
                        'fecha_movimiento' => now(),
                    ]);
                }
            }

            $nota->update([
                'estado' => 'confirmada',
                'confirmada_en' => now(),
            ]);
        });
    }
}
