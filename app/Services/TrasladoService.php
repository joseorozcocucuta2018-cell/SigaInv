<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\TrasladoEstado;
use App\Models\MovimientoInventario;
use App\Models\StockBodega;
use App\Models\Traslado;
use App\Models\TrasladoDetalle;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TrasladoService
{
    /**
     * Confirmar un traslado: mueve stock de bodega origen a destino
     */
    public function confirmar(Traslado $traslado): void
    {
        if (! $traslado->puedeConfirmar()) {
            throw new Exception('El traslado no puede ser confirmado. Debe estar en estado borrador y tener al menos un producto.');
        }

        DB::transaction(function () use ($traslado) {
            $traslado->load(['detalles.producto', 'bodegaOrigen', 'bodegaDestino']);

            foreach ($traslado->detalles as $detalle) {
                $this->moverStock(
                    traslado: $traslado,
                    detalle: $detalle,
                    bodegaOrigenId: $traslado->bodega_origen_id,
                    bodegaDestinoId: $traslado->bodega_destino_id,
                    tipoSalida: 'salida_traslado',
                    tipoEntrada: 'entrada_traslado',
                    observacionSalida: "Traslado #{$traslado->id} - Salida hacia bodega: {$traslado->bodegaDestino->nombre}",
                    observacionEntrada: "Traslado #{$traslado->id} - Entrada desde bodega: {$traslado->bodegaOrigen->nombre}",
                );
            }

            $traslado->confirmada_en = now();
            $traslado->update([
                'estado' => TrasladoEstado::CONFIRMADA,
                'usuario_id' => Auth::id() ?? $traslado->usuario_id,
            ]);
        });
    }

    /**
     * Anula un traslado en estado borrador
     */
    public function anular(Traslado $traslado): void
    {
        if ($traslado->estado !== TrasladoEstado::BORRADOR) {
            throw new Exception('Solo se pueden anular traslados en estado borrador.');
        }

        $traslado->update(['estado' => TrasladoEstado::ANULADA]);
    }

    /**
     * Revertir un traslado: devuelve stock a bodega origen
     */
    public function revertir(Traslado $traslado): void
    {
        if (! $traslado->puedeRevertir()) {
            throw new Exception('El traslado no puede ser revertido. Solo se pueden revertir traslados confirmados.');
        }

        DB::transaction(function () use ($traslado) {
            $traslado->load(['detalles.producto', 'bodegaOrigen', 'bodegaDestino']);

            foreach ($traslado->detalles as $detalle) {
                // Revertir: origen=destino original, destino=origen original
                $this->moverStock(
                    traslado: $traslado,
                    detalle: $detalle,
                    bodegaOrigenId: $traslado->bodega_destino_id,
                    bodegaDestinoId: $traslado->bodega_origen_id,
                    tipoSalida: 'reverso_traslado',
                    tipoEntrada: 'reverso_traslado',
                    observacionSalida: "Reversión traslado #{$traslado->id} - Salida de bodega: {$traslado->bodegaDestino->nombre}",
                    observacionEntrada: "Reversión traslado #{$traslado->id} - Retorno a bodega: {$traslado->bodegaOrigen->nombre}",
                );
            }

            $traslado->revertida_en = now();
            $traslado->update(['estado' => TrasladoEstado::REVERTIDA]);
        });
    }

    /**
     * Mueve stock entre dos bodegas y registra los movimientos de inventario
     */
    private function moverStock(
        Traslado $traslado,
        TrasladoDetalle $detalle,
        int $bodegaOrigenId,
        int $bodegaDestinoId,
        string $tipoSalida,
        string $tipoEntrada,
        string $observacionSalida,
        string $observacionEntrada,
    ): void {
        $productoId = $detalle->producto_id;
        $cantidad = (float) $detalle->cantidad;
        $costo = (float) ($detalle->producto->costo_promedio ?? $detalle->producto->precio_compra ?? 0);

        // Descontar de bodega origen (con lock para evitar race conditions)
        $stockOrigen = StockBodega::where('bodega_id', $bodegaOrigenId)
            ->where('producto_id', $productoId)
            ->lockForUpdate()
            ->first();

        if (! $stockOrigen || (float) $stockOrigen->cantidad < $cantidad) {
            $nombreProducto = $detalle->producto->nombre ?? "ID:{$productoId}";
            throw new Exception(
                "Stock insuficiente para '{$nombreProducto}'. Disponible: ".
                    ($stockOrigen ? $stockOrigen->cantidad : 0).", requerido: {$cantidad}."
            );
        }

        $stockOrigen->decrement('cantidad', $cantidad);
        $stockOrigen->refresh();
        $stockOrigenResultante = (float) $stockOrigen->cantidad;

        MovimientoInventario::create([
            'producto_id' => $productoId,
            'bodega_id' => $bodegaOrigenId,
            'tipo_movimiento' => $tipoSalida,
            'cantidad' => $cantidad,
            'costo_unitario' => $costo,
            'lote' => $detalle->lote,
            'fecha_vencimiento' => $detalle->fecha_vencimiento,
            'stock_resultante' => $stockOrigenResultante,
            'documento_tipo' => 'traslado',
            'documento_id' => $traslado->id,
            'observacion' => $observacionSalida,
            'usuario_id' => Auth::id() ?? $traslado->usuario_id,
            'fecha_movimiento' => now(),
        ]);

        // Incrementar en bodega destino (con lock para evitar race conditions)
        $stockDestino = StockBodega::where('bodega_id', $bodegaDestinoId)
            ->where('producto_id', $productoId)
            ->lockForUpdate()
            ->first();

        if (! $stockDestino) {
            $stockDestino = StockBodega::create([
                'bodega_id' => $bodegaDestinoId,
                'producto_id' => $productoId,
                'cantidad' => 0,
            ]);
            $stockDestino = StockBodega::where('bodega_id', $bodegaDestinoId)
                ->where('producto_id', $productoId)
                ->lockForUpdate()
                ->first();
        }

        $stockDestino->increment('cantidad', $cantidad);
        $stockDestino->refresh();
        $stockDestinoResultante = (float) $stockDestino->cantidad;

        MovimientoInventario::create([
            'producto_id' => $productoId,
            'bodega_id' => $bodegaDestinoId,
            'tipo_movimiento' => $tipoEntrada,
            'cantidad' => $cantidad,
            'costo_unitario' => $costo,
            'lote' => $detalle->lote,
            'fecha_vencimiento' => $detalle->fecha_vencimiento,
            'stock_resultante' => $stockDestinoResultante,
            'documento_tipo' => 'traslado',
            'documento_id' => $traslado->id,
            'observacion' => $observacionEntrada,
            'usuario_id' => Auth::id() ?? $traslado->usuario_id,
            'fecha_movimiento' => now(),
        ]);
    }
}
