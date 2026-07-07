<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AjusteEstado;
use App\Enums\MotivoAjuste;
use App\Models\AjusteInventario;
use App\Models\DetalleConteoFisico;
use App\Models\MovimientoInventario;
use App\Models\StockBodega;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AjusteInventarioService
{
    public function confirmar(AjusteInventario $ajuste): void
    {
        if ($ajuste->estado !== AjusteEstado::BORRADOR) {
            throw new \InvalidArgumentException('Solo se pueden confirmar ajustes en estado borrador.');
        }

        $esDeConteo = DetalleConteoFisico::where('ajuste_inventario_id', $ajuste->id)->exists();

        foreach ($ajuste->detalles as $detalle) {
            if ($esDeConteo) {
                $diferencia = (float) $detalle->diferencia;
            } elseif ((float) $detalle->diferencia !== 0.0) {
                $diferencia = (float) $detalle->diferencia;
            } else {
                $cantidad = (float) ($detalle->stock_fisico ?? 0);
                $diferencia = $ajuste->motivo?->esSuma() ? $cantidad : -$cantidad;
            }

            $stockBodega = StockBodega::where('producto_id', $detalle->producto_id)
                ->where('bodega_id', $ajuste->bodega_id)
                ->first();

            $detalle->update([
                'stock_sistema' => (float) ($stockBodega?->cantidad ?? 0),
                'diferencia' => $diferencia,
                'costo_unitario' => $detalle->producto?->costo_promedio ?? $detalle->costo_unitario,
            ]);
        }

        $detallesConDiferencia = $ajuste->detalles()->where('diferencia', '!=', 0)->get();

        if ($detallesConDiferencia->isEmpty()) {
            throw new \InvalidArgumentException('No hay diferencias que ajustar.');
        }

        DB::transaction(function () use ($ajuste, $detallesConDiferencia) {
            foreach ($detallesConDiferencia as $detalle) {
                // Sincronizar costo promedio si es saldo inicial
                if ($ajuste->motivo === MotivoAjuste::AJUSTE_INICIAL) {
                    $detalle->producto->update([
                        'costo_promedio' => $detalle->costo_unitario,
                        'precio_compra' => $detalle->costo_unitario,
                    ]);
                }

                // Regla 4: Bloquear el registro de stock para evitar race conditions
                $stock = StockBodega::where('producto_id', $detalle->producto_id)
                    ->where('bodega_id', $ajuste->bodega_id)
                    ->lockForUpdate()
                    ->first();

                if (! $stock) {
                    $stock = StockBodega::create([
                        'producto_id' => $detalle->producto_id,
                        'bodega_id' => $ajuste->bodega_id,
                        'cantidad' => 0,
                    ]);

                    // Volvemos a obtenerlo para aplicar el bloqueo correctamente
                    $stock = StockBodega::where('id', $stock->id)->lockForUpdate()->first();
                }

                $tipoMovimiento = match (true) {
                    $ajuste->motivo === MotivoAjuste::AJUSTE_INICIAL => 'ajuste_inicial',
                    $ajuste->motivo === MotivoAjuste::CONTEO_FISICO => 'ajuste_conteo',
                    $detalle->diferencia > 0 => 'ajuste_positivo',
                    default => 'ajuste_negativo',
                };

                $diferencia = (float) $detalle->diferencia;

                if ($diferencia > 0) {
                    $stock->increment('cantidad', $diferencia);
                } else {
                    $stock->decrement('cantidad', abs($diferencia));
                }

                $stock->refresh();

                MovimientoInventario::create([
                    'producto_id' => $detalle->producto_id,
                    'bodega_id' => $ajuste->bodega_id,
                    'tipo_movimiento' => $tipoMovimiento,
                    'cantidad' => abs($diferencia),
                    'costo_unitario' => $detalle->costo_unitario,
                    'stock_resultante' => $stock->cantidad,
                    'documento_tipo' => 'ajuste_inventario',
                    'documento_id' => $ajuste->id,
                    'observacion' => "Ajuste #{$ajuste->numero} — ".($ajuste->motivo?->label() ?? 'Sin motivo'),
                    'usuario_id' => Auth::id() ?? $ajuste->usuario_id,
                    'fecha_movimiento' => now(),
                ]);
            }

            $ajuste->update([
                'estado' => AjusteEstado::CONFIRMADO,
                'confirmado_en' => now(),
            ]);
        });
    }

    public function anular(AjusteInventario $ajuste): void
    {
        if ($ajuste->estado !== AjusteEstado::CONFIRMADO) {
            throw new \InvalidArgumentException('Solo se pueden anular ajustes confirmados.');
        }

        $detallesConDiferencia = $ajuste->detalles()->where('diferencia', '!=', 0)->get();

        DB::transaction(function () use ($ajuste, $detallesConDiferencia) {
            foreach ($detallesConDiferencia as $detalle) {
                $stock = StockBodega::where('producto_id', $detalle->producto_id)
                    ->where('bodega_id', $ajuste->bodega_id)
                    ->firstOrFail();

                $tipoMovimiento = match (true) {
                    $ajuste->motivo === MotivoAjuste::AJUSTE_INICIAL => 'ajuste_inicial',
                    $ajuste->motivo === MotivoAjuste::CONTEO_FISICO => 'ajuste_conteo',
                    $detalle->diferencia > 0 => 'ajuste_negativo',
                    default => 'ajuste_positivo',
                };

                $diferencia = (float) $detalle->diferencia;

                if ($diferencia > 0) {
                    $stock->decrement('cantidad', $diferencia);
                } else {
                    $stock->increment('cantidad', abs($diferencia));
                }

                $stock->refresh();

                MovimientoInventario::create([
                    'producto_id' => $detalle->producto_id,
                    'bodega_id' => $ajuste->bodega_id,
                    'tipo_movimiento' => $tipoMovimiento,
                    'cantidad' => abs($diferencia),
                    'costo_unitario' => $detalle->costo_unitario,
                    'stock_resultante' => $stock->cantidad,
                    'documento_tipo' => 'ajuste_inventario',
                    'documento_id' => $ajuste->id,
                    'observacion' => "Anulación ajuste #{$ajuste->numero}",
                    'usuario_id' => Auth::id() ?? $ajuste->usuario_id,
                    'fecha_movimiento' => now(),
                ]);
            }

            $ajuste->update([
                'estado' => AjusteEstado::ANULADO,
            ]);
        });
    }
}
