<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\CompraEstado;
use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\HistoricoPrecio;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\StockBodega;
use App\Models\StockBodegaLote;
use App\Models\StockBodegaSerial;
use App\Models\User;
use App\Notifications\CompraConfirmadaNotification;
use App\Traits\LoggingCriticoInventario;
use App\Traits\RegistraAuditoria;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use InvalidArgumentException;

/**
 * Servicio para operaciones de Compra
 * Maneja confirmación y cambios de estado
 */
class CompraService
{
    use LoggingCriticoInventario, RegistraAuditoria;

    /**
     * Registra una compra y genera movimientos de inventario
     *
     * @throws InvalidArgumentException Si no se puede registrar
     */
    public static function registrar(Compra $compra): void
    {
        if (! in_array(CompraEstado::REGISTRADA, $compra->estado->validTransitions())) {
            throw new InvalidArgumentException(
                "No se puede registrar una compra en estado {$compra->estado->label()}. Transición no permitida."
            );
        }

        // Validar totales
        if ((float) $compra->total < 0) {
            throw new InvalidArgumentException('El total de la compra no puede ser negativo.');
        }

        if ((float) $compra->descuento > (float) $compra->subtotal) {
            throw new InvalidArgumentException(
                'El descuento no puede ser mayor que el subtotal.'
            );
        }

        DB::beginTransaction();

        try {
            // Bloquear fila para evitar doble confirmación concurrente
            $compra = Compra::lockForUpdate()->find($compra->id);

            // Validar detalles y datos mínimos
            $compra->validarRegistrable();

            // Snapshot de stock y costo por producto ANTES de actualizar (para costo promedio ponderado)
            $productosData = [];
            foreach ($compra->detalles as $detalle) {
                $pid = $detalle->producto_id;
                if (! isset($productosData[$pid])) {
                    $producto = Producto::find($pid);
                    $productosData[$pid] = [
                        'stock_antes' => StockService::getTotalStockForProduct($pid),
                        'costo_antes' => $producto ? (float) ($producto->costo_promedio ?? $producto->precio_compra) : 0,
                        'entrada_cantidad' => 0,
                        'entrada_valor' => 0.0,
                    ];
                }
                $productosData[$pid]['entrada_cantidad'] += (float) $detalle->cantidad;
                $productosData[$pid]['entrada_valor'] += (float) $detalle->cantidad * (float) $detalle->precio_unitario;
            }

            // Generar movimientos de inventario
            foreach ($compra->detalles as $detalle) {
                static::crearMovimientoCompra($compra, $detalle);
            }

            // Recalcular costo promedio ponderado y actualizar producto
            foreach ($productosData as $productoId => $data) {
                $stockAntes = $data['stock_antes'];
                $entradaCant = $data['entrada_cantidad'];
                $entradaValor = $data['entrada_valor'];
                $stockDespues = $stockAntes + $entradaCant;

                if ($stockAntes > 0 && $stockDespues > 0) {
                    // Costo promedio ponderado con stock positivo
                    CostoPromedioService::calcularCostoPromedio(
                        $productoId,
                        $stockAntes,                    // stock ANTES de la operación
                        $entradaCant,                   // cantidad de entrada
                        $entradaValor / $entradaCant    // costo unitario promedio
                    );
                } else {
                    // Stock cero o negativo: usar el costo de entrada directamente
                    $costoUnitario = round($entradaValor / $entradaCant, 4);
                    Producto::where('id', $productoId)->update([
                        'costo_promedio' => $costoUnitario,
                        'precio_compra' => round($costoUnitario, 2),
                    ]);
                }
            }

            // Registrar histórico de precios por proveedor (para análisis de precios)
            static::registrarHistoricoPrecios($compra);

            // Cambiar estado y registrar fecha de confirmación con snapshot
            $estadoAnterior = $compra->estado;
            $compra->confirmada_en = now();
            $compra->update([
                'estado' => CompraEstado::REGISTRADA,
                'total_confirmado' => $compra->total,
                'impuestos_confirmados' => $compra->impuestos,
                'snapshot_confirmacion' => json_encode([
                    'subtotal' => $compra->subtotal,
                    'descuento' => $compra->descuento,
                    'impuestos' => $compra->impuestos,
                    'total' => $compra->total,
                    'saldo_pendiente' => $compra->saldo_pendiente,
                    'usuario_id' => Auth::id(),
                    'fecha_confirmacion' => now()->toIso8601String(),
                ]),
            ]);

            // Registrar en auditoría
            static::registrarAuditoria(
                documentoTipo: 'compra',
                documentoId: $compra->id,
                accion: 'registrar',
                campo: 'estado',
                valorAnterior: $estadoAnterior->value,
                valorNuevo: CompraEstado::REGISTRADA->value,
                estadoDocumento: CompraEstado::REGISTRADA->value,
                observacion: 'Compra registrada. Se generaron movimientos de inventario y se recalculó costo promedio.',
            );

            // Actualizar saldo del proveedor: la compra registrada es deuda
            $compra->proveedor->increment('saldo', $compra->total);

            DB::commit();
            static::logConfirmacionExitosa('compra', $compra->id, $compra->detalles->toArray());

            // Notificar a administradores y contadores (fuera de la transacción)
            try {
                $users = User::role(['administrador', 'contador'])->get();
                Notification::send($users, new CompraConfirmadaNotification($compra));
            } catch (\Exception) {
                // No bloquear la confirmación si la notificación falla
            }
        } catch (\Exception $e) {
            DB::rollBack();
            static::logConfirmacionFallida('compra', $compra->id ?? 0, $e);
            throw $e;
        }
    }

    /**
     * Crea movimiento de inventario para un detalle de compra
     */
    private static function crearMovimientoCompra(Compra $compra, DetalleCompra $detalle): void
    {
        $bodegaId = $compra->bodega_id;
        $productoId = $detalle->producto_id;
        $cantidad = $detalle->cantidad;
        $lote = $detalle->lote;
        $vence = $detalle->fecha_vencimiento;
        $serial = $detalle->serial;

        // Validar cantidad
        StockService::validateQuantity($cantidad);

        // Verificar si ya existe movimiento para este detalle
        if (MovimientoInventario::where('documento_tipo', 'compra')
            ->where('documento_id', $compra->id)
            ->where('detalle_compra_id', $detalle->id)
            ->exists()
        ) {
            throw new InvalidArgumentException(
                "Ya existe movimiento registrado para este detalle de compra (ID: {$detalle->id})"
            );
        }

        // 1. Actualizar Stock
        if ($lote !== null) {
            $stock = StockBodega::firstOrCreate(
                ['producto_id' => $productoId, 'bodega_id' => $bodegaId],
                ['cantidad' => 0]
            );
            $stockLote = StockBodegaLote::where('stock_bodega_id', $stock->id)
                ->where('lote', $lote)
                ->when($vence !== null, function ($q) use ($vence) {
                    $q->whereDate('fecha_vencimiento', $vence);
                })->first();

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
            $stock = StockBodega::firstOrCreate(
                ['producto_id' => $productoId, 'bodega_id' => $bodegaId],
                ['cantidad' => 0]
            );
            $stock->increment('cantidad', $cantidad);
            $stock->refresh();
            $stockResult = $stock->cantidad;
        }

        // 1.b Crear serial si aplica
        $producto = $detalle->producto;
        if ($producto && $producto->usaSeriales()) {
            if ((float) $cantidad !== 1.0) {
                throw new InvalidArgumentException('Los productos controlados por serial deben ingresarse con cantidad 1 por línea de compra.');
            }

            $serial = trim((string) $serial);
            if ($serial === '') {
                throw new InvalidArgumentException('Debe ingresar un número de serie para el producto controlado por serial.');
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

        // 2. Registrar Movimiento
        MovimientoInventario::create([
            'producto_id' => $productoId,
            'bodega_id' => $bodegaId,
            'tipo_movimiento' => 'entrada_compra',
            'cantidad' => $cantidad,
            'costo_unitario' => $detalle->precio_unitario,
            'lote' => $lote,
            'fecha_vencimiento' => $vence,
            'stock_resultante' => $stockResult,
            'documento_tipo' => 'compra',
            'documento_id' => $compra->id,
            'detalle_compra_id' => $detalle->id,
            'observacion' => "Entrada por Compra #{$compra->numero}",
            'usuario_id' => Auth::id() ?? $compra->usuario_id,
            'fecha_movimiento' => now(),
        ]);
    }

    /**
     * Cambia estado de compra a pagada
     */
    public static function marcarPagada(Compra $compra): void
    {
        if (! in_array(CompraEstado::PAGADA, $compra->estado->validTransitions())) {
            throw new InvalidArgumentException(
                "No se puede marcar como pagada una compra en estado {$compra->estado->label()}. Transición no permitida."
            );
        }

        // Si está en borrador, registrar primero
        if ($compra->estado?->value === CompraEstado::BORRADOR->value) {
            static::registrar($compra);
        }

        $estadoAnterior = $compra->estado;
        $compra->update([
            'estado' => CompraEstado::PAGADA,
            'saldo_pendiente' => 0,
        ]);

        // Registrar en auditoría
        static::registrarAuditoria(
            documentoTipo: 'compra',
            documentoId: $compra->id,
            accion: 'mark_paid',
            campo: 'estado',
            valorAnterior: $estadoAnterior->value,
            valorNuevo: CompraEstado::PAGADA->value,
            estadoDocumento: CompraEstado::PAGADA->value,
            observacion: 'Compra marcada como pagada.',
        );
    }

    /**
     * Anula una compra
     * Genera movimientos inversos si está confirmada
     */
    public static function anular(Compra $compra, ?string $razon = null): void
    {
        // Recargar estado desde DB para evitar estado stale
        $compra = Compra::lockForUpdate()->find($compra->id) ?? $compra;

        if (! in_array(CompraEstado::ANULADA, $compra->estado->validTransitions())) {
            throw new InvalidArgumentException(
                "No se puede anular una compra en estado {$compra->estado->label()}. Transición no permitida."
            );
        }

        DB::beginTransaction();

        try {
            $estadoAnterior = $compra->estado;

            // Si está registrada, revertir movimientos y recalcular CPP
            if ($compra->estado?->value === CompraEstado::REGISTRADA->value) {
                // Snapshot de stock y costo por producto ANTES de revertir (para recalcular CPP)
                $productosData = [];
                foreach ($compra->detalles as $detalle) {
                    $pid = $detalle->producto_id;
                    if (! isset($productosData[$pid])) {
                        $producto = Producto::find($pid);
                        $productosData[$pid] = [
                            'stock_antes' => StockService::getTotalStockForProduct($pid),
                            'costo_antes' => $producto ? (float) ($producto->costo_promedio ?? $producto->precio_compra) : 0,
                            'salida_cantidad' => 0,
                            'salida_valor' => 0.0,
                        ];
                    }
                    $productosData[$pid]['salida_cantidad'] += (float) $detalle->cantidad;
                    $productosData[$pid]['salida_valor'] += (float) $detalle->cantidad * (float) $detalle->precio_unitario;
                }

                foreach ($compra->detalles as $detalle) {
                    static::revertirMovimientoCompra($compra, $detalle);
                }

                // Recalcular CPP tras revertir el stock usando el servicio
                foreach ($productosData as $productoId => $data) {
                    $salidaCant = $data['salida_cantidad'];
                    $costoUnitario = $salidaCant > 0 ? $data['salida_valor'] / $salidaCant : 0;

                    // Usar el CostoPromedioService para revertir
                    CostoPromedioService::revertirCostoPromedio($productoId, $salidaCant, $costoUnitario);
                }
            }

            $compra->update([
                'estado' => CompraEstado::ANULADA,
            ]);

            static::registrarAuditoria(
                documentoTipo: 'compra',
                documentoId: $compra->id,
                accion: 'cancel',
                campo: 'estado',
                valorAnterior: $estadoAnterior->value,
                valorNuevo: CompraEstado::ANULADA->value,
                estadoDocumento: CompraEstado::ANULADA->value,
                observacion: $razon ?? 'Compra anulada sin especificar razón.',
            );

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Registra histórico de precios por proveedor al confirmar compra
     * Si ya existe registro para producto+proveedor → actualiza
     * Si no existe → crea nuevo registro
     */
    private static function registrarHistoricoPrecios(Compra $compra): void
    {
        $proveedorId = $compra->proveedor_id;

        foreach ($compra->detalles as $detalle) {
            $producto = $detalle->producto;

            if (! $producto) {
                continue;
            }

            $precioUnitario = (float) $detalle->precio_unitario;

            // Buscar si ya existe registro para este producto+proveedor
            $historico = HistoricoPrecio::where('producto_id', $detalle->producto_id)
                ->where('proveedor_id', $proveedorId)
                ->first();

            if ($historico) {
                // Actualizar precio existente
                $historico->update([
                    'precio_compra' => $precioUnitario,
                    'usuario_id' => Auth::id(),
                    'fecha_cambio' => now(),
                ]);
            } else {
                // Crear nuevo registro
                HistoricoPrecio::create([
                    'producto_id' => $detalle->producto_id,
                    'proveedor_id' => $proveedorId,
                    'precio_compra' => $precioUnitario,
                    'usuario_id' => Auth::id(),
                    'fecha_cambio' => now(),
                ]);
            }
        }
    }

    /**
     * Revierte movimiento de inventario (anulación)
     */
    private static function revertirMovimientoCompra(Compra $compra, DetalleCompra $detalle): void
    {
        $bodegaId = $compra->bodega_id;
        $productoId = $detalle->producto_id;
        $cantidad = $detalle->cantidad;
        $lote = $detalle->lote;
        $vence = $detalle->fecha_vencimiento;

        // Revertir stock
        if ($lote !== null) {
            $stockLote = StockBodegaLote::whereHas('stock', function ($q) use ($productoId, $bodegaId) {
                $q->where('producto_id', $productoId)
                    ->where('bodega_id', $bodegaId);
            })->where('lote', $lote)
                ->when($vence !== null, function ($q) use ($vence) {
                    $q->whereDate('fecha_vencimiento', $vence);
                })->first();

            if ($stockLote) {
                $stockLote->decrement('cantidad', $cantidad);
                $stockLote->refresh();
            }
            $stockResult = $stockLote ? $stockLote->cantidad : 0;
        } else {
            $stock = StockBodega::where('producto_id', $productoId)
                ->where('bodega_id', $bodegaId)
                ->first();

            if ($stock) {
                $stock->decrement('cantidad', $cantidad);
                $stock->refresh();
            }
            $stockResult = $stock ? $stock->cantidad : 0;
        }

        // Registrar movimiento inverso
        MovimientoInventario::create([
            'producto_id' => $productoId,
            'bodega_id' => $bodegaId,
            'tipo_movimiento' => 'reverso_anulacion',
            'cantidad' => $cantidad,
            'costo_unitario' => $detalle->precio_unitario,
            'lote' => $lote,
            'fecha_vencimiento' => $vence,
            'stock_resultante' => $stockResult,
            'documento_tipo' => 'compra',
            'documento_id' => $compra->id,
            'detalle_compra_id' => $detalle->id,
            'observacion' => "Anulación de Compra #{$compra->numero}",
            'usuario_id' => Auth::id() ?? $compra->usuario_id,
            'fecha_movimiento' => now(),
        ]);
    }
}
