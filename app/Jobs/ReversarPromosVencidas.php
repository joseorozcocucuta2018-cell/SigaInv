<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\TransformacionEstado;
use App\Enums\TransformacionTipo;
use App\Models\MovimientoInventario;
use App\Models\StockBodega;
use App\Models\StockBodegaLote;
use App\Models\Transformacion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job para reversar automáticamente promociones vencidas
 *
 * Este job se ejecuta diariamente y busca promociones confirmadas
 * cuya fecha de vencimiento ya pasó, y las revierte automáticamente
 * si sus productos no han sido vendidos.
 */
class ReversarPromosVencidas implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $promosVencidas = $this->obtenerPromosVencidas();

        Log::info("Job ReversarPromosVencidas: Se encontraron {$promosVencidas->count()} promociones vencidas");

        foreach ($promosVencidas as $promo) {
            try {
                $this->procesarReversion($promo);
            } catch (\Exception $e) {
                Log::error("Error al revertir promo {$promo->id}: ".$e->getMessage());
            }
        }
    }

    /**
     * Obtiene las promociones vencidas que pueden revertirse
     */
    protected function obtenerPromosVencidas(): Collection
    {
        return Transformacion::where('tipo', TransformacionTipo::PROMO->value)
            ->where('estado', TransformacionEstado::CONFIRMADA->value)
            ->whereDate('fecha_vencimiento', '<', now()->toDateString())
            ->whereNull('revertida_en')
            ->get();
    }

    /**
     * Procesa la reversión de una promoción
     */
    protected function procesarReversion(Transformacion $promo): void
    {
        // Validar que los productos resultantes no hayan sido vendidos
        if ($this->productosFueronVendidos($promo)) {
            Log::warning("Promo {$promo->id} no puede revertirse: productos ya vendidos");

            return;
        }

        // Revertir stock: restaurar insumos, quitar productos
        $this->revertirStock($promo);

        // Actualizar estado
        $promo->update([
            'estado' => TransformacionEstado::REVERTIDA->value,
            'revertida_en' => now(),
        ]);

        // Registrar en auditoría
        Log::info("Promo {$promo->id} revertida automáticamente por vencimiento");
    }

    /**
     * Verifica si los productos resultado ya fueron vendidos
     */
    protected function productosFueronVendidos(Transformacion $promo): bool
    {
        $productos = $promo->productos()->get();

        foreach ($productos as $producto) {
            // Verificar si hay movimientos de venta para este producto
            $ventas = MovimientoInventario::where('producto_id', $producto->producto_id)
                ->where('tipo_movimiento', 'salida_venta')
                ->where('documento_tipo', 'venta')
                ->where('documento_id', '>', 0)
                ->exists();

            if ($ventas) {
                return true;
            }
        }

        return false;
    }

    /**
     * Revierte el stock: restaura insumos y elimina productos resultado
     */
    protected function revertirStock(Transformacion $promo): void
    {
        $bodegaId = $promo->bodega_id;

        // 1. Restaurar stock de insumos (devolver al inventario)
        foreach ($promo->insumos()->get() as $insumo) {
            $this->incrementarStock(
                $insumo->producto_id,
                $bodegaId,
                $insumo->cantidad,
                $insumo->lote,
                $insumo->fecha_vencimiento
            );

            // Registrar movimiento
            MovimientoInventario::create([
                'producto_id' => $insumo->producto_id,
                'bodega_id' => $bodegaId,
                'tipo_movimiento' => 'entrada_reversion_promo',
                'cantidad' => $insumo->cantidad,
                'costo_unitario' => $insumo->costo_unitario,
                'lote' => $insumo->lote,
                'fecha_vencimiento' => $insumo->fecha_vencimiento,
                'stock_resultante' => $this->obtenerStock($insumo->producto_id, $bodegaId),
                'documento_tipo' => 'transformacion',
                'documento_id' => $promo->id,
                'observacion' => "Reversión automática por vencimiento de promoción #{$promo->id}",
                'usuario_id' => $promo->usuario_id,
                'fecha_movimiento' => now(),
            ]);
        }

        // 2. Eliminar stock de productos resultado
        foreach ($promo->productos()->get() as $producto) {
            $this->decrementarStock(
                $producto->producto_id,
                $bodegaId,
                $producto->cantidad,
                $producto->lote,
                $producto->fecha_vencimiento
            );

            // Registrar movimiento
            MovimientoInventario::create([
                'producto_id' => $producto->producto_id,
                'bodega_id' => $bodegaId,
                'tipo_movimiento' => 'salida_reversion_promo',
                'cantidad' => $producto->cantidad,
                'costo_unitario' => $producto->costo_unitario,
                'lote' => $producto->lote,
                'fecha_vencimiento' => $producto->fecha_vencimiento,
                'stock_resultante' => $this->obtenerStock($producto->producto_id, $bodegaId),
                'documento_tipo' => 'transformacion',
                'documento_id' => $promo->id,
                'observacion' => "Reversión automática por vencimiento de promoción #{$promo->id}",
                'usuario_id' => $promo->usuario_id,
                'fecha_movimiento' => now(),
            ]);
        }
    }

    /**
     * Incrementa el stock de un producto
     */
    protected function incrementarStock(int $productoId, int $bodegaId, float $cantidad, ?string $lote, $fechaVencimiento): void
    {
        if ($lote !== null) {
            $stock = StockBodega::firstOrCreate(
                ['producto_id' => $productoId, 'bodega_id' => $bodegaId],
                ['cantidad' => 0]
            );

            $stockLote = StockBodegaLote::where('stock_bodega_id', $stock->id)
                ->where('lote', $lote)
                ->when($fechaVencimiento !== null, function ($q) use ($fechaVencimiento) {
                    $q->whereDate('fecha_vencimiento', $fechaVencimiento);
                })
                ->first();

            if ($stockLote) {
                $stockLote->increment('cantidad', $cantidad);
            } else {
                StockBodegaLote::create([
                    'stock_bodega_id' => $stock->id,
                    'lote' => $lote,
                    'fecha_vencimiento' => $fechaVencimiento,
                    'cantidad' => $cantidad,
                ]);
            }
        } else {
            $stock = StockBodega::firstOrCreate(
                ['producto_id' => $productoId, 'bodega_id' => $bodegaId],
                ['cantidad' => 0]
            );
            $stock->increment('cantidad', $cantidad);
        }
    }

    /**
     * Decrementa el stock de un producto
     */
    protected function decrementarStock(int $productoId, int $bodegaId, float $cantidad, ?string $lote, $fechaVencimiento): void
    {
        if ($lote !== null) {
            $stock = StockBodega::where('producto_id', $productoId)
                ->where('bodega_id', $bodegaId)
                ->first();

            if ($stock) {
                $stockLote = StockBodegaLote::where('stock_bodega_id', $stock->id)
                    ->where('lote', $lote)
                    ->when($fechaVencimiento !== null, function ($q) use ($fechaVencimiento) {
                        $q->whereDate('fecha_vencimiento', $fechaVencimiento);
                    })
                    ->first();

                if ($stockLote) {
                    $stockLote->decrement('cantidad', $cantidad);
                }
            }
        } else {
            $stock = StockBodega::where('producto_id', $productoId)
                ->where('bodega_id', $bodegaId)
                ->first();

            if ($stock) {
                $stock->decrement('cantidad', $cantidad);
            }
        }
    }

    /**
     * Obtiene el stock actual de un producto
     */
    protected function obtenerStock(int $productoId, int $bodegaId): float
    {
        $stock = StockBodega::where('producto_id', $productoId)
            ->where('bodega_id', $bodegaId)
            ->first();

        return $stock ? (float) $stock->cantidad : 0;
    }
}
