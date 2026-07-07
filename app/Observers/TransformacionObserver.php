<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\TransformacionEstado;
use App\Models\MovimientoInventario;
use App\Models\StockBodega;
use App\Models\StockBodegaLote;
use App\Models\StockBodegaSerial;
use App\Models\Transformacion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TransformacionObserver
{
    public function updated(Transformacion $transformacion): void
    {
        // Solo actuar cuando cambia a estado confirmada
        if (! $transformacion->wasChanged('estado')) {
            return;
        }

        if ($transformacion->estado !== TransformacionEstado::CONFIRMADA) {
            return;
        }

        // Evitar reprocesar si ya tiene movimientos ligados
        if (MovimientoInventario::where('documento_tipo', 'transformacion')
            ->where('documento_id', $transformacion->id)
            ->exists()
        ) {
            return;
        }

        DB::transaction(function () use ($transformacion) {
            $bodegaId = $transformacion->bodega_id;
            $usuarioId = Auth::id() ?? $transformacion->usuario_id;

            // 1. Calcular costo total de insumos
            $costoTotalInsumos = 0;
            foreach ($transformacion->detalles as $detalle) {
                if ($detalle->tipo_linea === 'insumo') {
                    $costoTotalInsumos += (float) ($detalle->cantidad * $detalle->costo_unitario);
                }
            }

            foreach ($transformacion->detalles as $detalle) {
                $producto = $detalle->producto;
                $productoId = $detalle->producto_id;
                $cantidad = (float) $detalle->cantidad;
                $lote = $detalle->lote;
                $vence = $detalle->fecha_vencimiento;

                // Si es el producto final, asignar el costo calculado
                $costoUnit = ($detalle->tipo_linea !== 'insumo')
                    ? ($cantidad > 0 ? ($costoTotalInsumos / $cantidad) : 0)
                    : (float) ($detalle->costo_unitario ?? 0);

                if ($cantidad <= 0) {
                    throw new InvalidArgumentException('La cantidad en una línea de transformación debe ser mayor que 0.');
                }

                // Validaciones específicas para seriales
                $serial = null;
                $serialModel = null;
                if ($producto && $producto->usaSeriales()) {
                    if ($cantidad !== 1.0) {
                        throw new InvalidArgumentException('Los productos controlados por serial deben manejarse con cantidad 1 por línea en transformaciones.');
                    }

                    $serial = trim((string) ($detalle->serial ?? ''));
                    if ($detalle->tipo_linea === 'insumo') {
                        if ($serial === '') {
                            throw new InvalidArgumentException('Debe indicar un número de serie para la línea de insumo controlada por serial.');
                        }

                        $serialModel = StockBodegaSerial::where('serial', $serial)
                            ->where('status', 'available')
                            ->first();

                        if (! $serialModel) {
                            throw new InvalidArgumentException("El número de serie '{$serial}' no está disponible en inventario para ser consumido.");
                        }
                    }
                }

                // Obtener o crear stock base
                $stock = StockBodega::firstOrCreate(
                    ['producto_id' => $productoId, 'bodega_id' => $bodegaId],
                    ['cantidad' => 0]
                );

                // Decidir tipo de movimiento
                if ($detalle->tipo_linea === 'insumo') {
                    // Salida de insumo
                    if ($lote !== null) {
                        $stockLote = StockBodegaLote::where('stock_bodega_id', $stock->id)
                            ->where('lote', $lote)
                            ->when($vence !== null, function ($q) use ($vence) {
                                $q->whereDate('fecha_vencimiento', $vence);
                            })
                            ->first();

                        if (! $stockLote || $stockLote->cantidad < $cantidad) {
                            throw new InvalidArgumentException('Stock insuficiente por lote para insumo en transformación.');
                        }

                        $stockLote->decrement('cantidad', $cantidad);
                        $stockLote->refresh();
                        $stockResult = $stockLote->cantidad;
                    } else {
                        if ($stock->cantidad < $cantidad) {
                            throw new InvalidArgumentException('Stock insuficiente para insumo en transformación.');
                        }

                        $stock->decrement('cantidad', $cantidad);
                        $stock->refresh();
                        $stockResult = $stock->cantidad;
                    }

                    if ($serialModel) {
                        $serialModel->update(['status' => 'used']);
                    }

                    MovimientoInventario::create([
                        'producto_id' => $productoId,
                        'bodega_id' => $bodegaId,
                        'tipo_movimiento' => 'salida_transformacion',
                        'cantidad' => $cantidad,
                        'costo_unitario' => $costoUnit,
                        'lote' => $lote,
                        'fecha_vencimiento' => $vence,
                        'stock_resultante' => $stockResult,
                        'documento_tipo' => 'transformacion',
                        'documento_id' => $transformacion->id,
                        'observacion' => "Consumo por transformación #{$transformacion->id}",
                        'usuario_id' => $usuarioId,
                        'fecha_movimiento' => now(),
                    ]);
                } else {
                    // Entrada de producto terminado
                    if ($lote !== null) {
                        $stockLote = StockBodegaLote::where('stock_bodega_id', $stock->id)
                            ->where('lote', $lote)
                            ->when($vence !== null, function ($q) use ($vence) {
                                $q->whereDate('fecha_vencimiento', $vence);
                            })
                            ->first();

                        if (! $stockLote) {
                            $stockLote = StockBodegaLote::create([
                                'stock_bodega_id' => $stock->id,
                                'lote' => $lote,
                                'fecha_vencimiento' => $vence,
                                'cantidad' => 0,
                            ]);
                        }

                        $stockLote->increment('cantidad', $cantidad);
                        $stockLote->refresh();
                        $stockResult = $stockLote->cantidad;
                    } else {
                        $stock->increment('cantidad', $cantidad);
                        $stock->refresh();
                        $stockResult = $stock->cantidad;
                    }

                    if ($producto && $producto->usaSeriales()) {
                        // Para productos terminados con serial, creamos un registro por línea
                        if ($serial === '' || $serial === null) {
                            throw new InvalidArgumentException('Debe indicar un número de serie para el producto terminado controlado por serial.');
                        }

                        if (StockBodegaSerial::where('serial', $serial)->exists()) {
                            throw new InvalidArgumentException("El número de serie '{$serial}' ya existe en el inventario.");
                        }

                        StockBodegaSerial::create([
                            'stock_bodega_id' => $stock->id,
                            'serial' => $serial,
                            'status' => 'available',
                            'lote' => $lote,
                            'fecha_vencimiento' => $vence,
                        ]);
                    }

                    MovimientoInventario::create([
                        'producto_id' => $productoId,
                        'bodega_id' => $bodegaId,
                        'tipo_movimiento' => 'entrada_transformacion',
                        'cantidad' => $cantidad,
                        'costo_unitario' => $costoUnit,
                        'lote' => $lote,
                        'fecha_vencimiento' => $vence,
                        'stock_resultante' => $stockResult,
                        'documento_tipo' => 'transformacion',
                        'documento_id' => $transformacion->id,
                        'observacion' => "Producción por transformación #{$transformacion->id}",
                        'usuario_id' => $usuarioId,
                        'fecha_movimiento' => now(),
                    ]);
                }
            }
        });
    }
}
