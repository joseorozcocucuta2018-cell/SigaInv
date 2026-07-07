<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ClienteEstado;
use App\Enums\EstadoPagoEnum;
use App\Enums\RemisionEstado;
use App\Enums\TurnoEstado;
use App\Enums\VentaEstado;
use App\Models\Bodega;
use App\Models\Caja;
use App\Models\Cliente;
use App\Models\DetalleVenta;
use App\Models\Empresa;
use App\Models\FormaPago;
use App\Models\MovimientoCaja;
use App\Models\MovimientoInventario;
use App\Models\Numeracion;
use App\Models\Producto;
use App\Models\StockBodega;
use App\Models\StockBodegaLote;
use App\Models\StockBodegaSerial;
use App\Models\Turno;
use App\Models\User;
use App\Models\Venta;
use App\Notifications\StockBajoNotification;
use App\Notifications\VentaConfirmadaNotification;
use App\Observers\CotizacionObserver;
use App\Traits\LoggingCriticoInventario;
use App\Traits\RegistraAuditoria;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use InvalidArgumentException;

/**
 * Servicio para operaciones de Venta
 * Maneja confirmación y cambios de estado
 */
class VentaService
{
    use LoggingCriticoInventario, RegistraAuditoria;

    /**
     * Confirma una venta y genera movimientos de inventario
     *
     * Casos:
     * 1. Venta CON Remisión ($venta->remision_id): NO descuenta (remisión ya lo hizo)
     * 2. Venta SIN Remisión ($venta->remision_id NULL): SÍ descuenta
     *
     * @throws InvalidArgumentException Si no se puede confirmar
     */
    public static function confirmar(Venta $venta): void
    {
        if (! in_array(VentaEstado::CONFIRMADA, $venta->estado->validTransitions())) {
            throw new InvalidArgumentException(
                "No se puede confirmar una venta en estado {$venta->estado->label()}. Transición no permitida."
            );
        }

        // Validar totales (req. 10)
        if ((float) $venta->total < 0) {
            throw new InvalidArgumentException('El total de la venta no puede ser negativo.');
        }

        if ((float) $venta->descuento > (float) $venta->subtotal) {
            throw new InvalidArgumentException(
                'El descuento no puede ser mayor que el subtotal.'
            );
        }

        // Validar resolución DIAN para facturación electrónica
        $tieneResolucion = Numeracion::where('tipo_documento', 'venta')
            ->whereNotNull('resolucion_numero')
            ->exists();

        if (! $tieneResolucion) {
            throw new InvalidArgumentException(
                'No se puede confirmar la venta sin una resolución DIAN activa. Configure la numeración en Configuración > Numeraciones.'
            );
        }

        DB::beginTransaction();

        try {
            // Bloquear fila para evitar doble confirmación concurrente (req. 4)
            $venta = Venta::lockForUpdate()->find($venta->id);

            // Validar detalles y datos mínimos
            $venta->validarConfirmable();

            // Generar movimientos de inventario solo si NO está vinculada a remisión
            if ($venta->remision_id === null) {
                foreach ($venta->detalles as $detalle) {
                    static::crearMovimientoVenta($venta, $detalle);
                }
            } else {
                // Si está vinculada a remisión, registrar solo como observación (sin afectar stock)
                foreach ($venta->detalles as $detalle) {
                    static::registrarMovimientoVentaDesdeRemision($venta, $detalle);
                }

                // Marcar la remisión como FACTURADA
                $venta->remision?->update(['estado' => RemisionEstado::FACTURADA]);
            }

            // Cambiar estado y registrar fecha de confirmación con snapshot
            $estadoAnterior = $venta->estado;
            $venta->confirmada_en = now();
            $venta->update([
                'estado' => VentaEstado::CONFIRMADA,
                'total_confirmado' => $venta->total,
                'impuestos_confirmados' => $venta->impuestos,
                'snapshot_confirmacion' => json_encode([
                    'subtotal' => $venta->subtotal,
                    'descuento' => $venta->descuento,
                    'impuestos' => $venta->impuestos,
                    'total' => $venta->total,
                    'saldo_pendiente' => $venta->saldo_pendiente,
                    'usuario_id' => Auth::id(),
                    'fecha_confirmacion' => now()->toIso8601String(),
                ]),
            ]);

            // Registrar en auditoría
            static::registrarAuditoria(
                documentoTipo: 'venta',
                documentoId: $venta->id,
                accion: 'confirm',
                campo: 'estado',
                valorAnterior: $estadoAnterior->value,
                valorNuevo: VentaEstado::CONFIRMADA->value,
                estadoDocumento: VentaEstado::CONFIRMADA->value,
                observacion: 'Venta confirmada. Se generaron movimientos de inventario.',
            );

            DB::commit();
            static::logConfirmacionExitosa('venta', $venta->id, $venta->detalles->toArray());

            if ($venta->cotizacion_id) {
                $cotizacion = $venta->cotizacion;
                if ($cotizacion) {
                    CotizacionObserver::validateForUse($cotizacion);
                    CotizacionService::marcarAceptada($cotizacion);
                }
            }

            // Incrementar saldo del cliente (deuda)
            $venta->cliente?->increment('saldo', $venta->total);

            // Enviar PDF por email al cliente (silencioso si no tiene email)
            try {
                app(DocumentoEmailService::class)->enviarDocumento($venta, 'venta');
            } catch (\Throwable $e) {
                Log::warning("No se pudo enviar email de venta {$venta->id}: ".$e->getMessage());
            }

            // Notificar stock bajo tras descontar (fuera de la transacción)
            if ($venta->remision_id === null) {
                static::notificarStockBajo($venta);
            }

            // Notificar a administradores (fuera de la transacción)
            try {
                $users = User::role('administrador')->get();
                Notification::send($users, new VentaConfirmadaNotification($venta));
            } catch (\Exception) {
                // No bloquear la confirmación si la notificación falla
            }
        } catch (\Exception $e) {
            DB::rollBack();
            static::logConfirmacionFallida('venta', $venta->id ?? 0, $e);
            throw $e;
        }
    }

    /**
     * Crea movimiento de inventario para un detalle de venta (sin remisión)
     * NOTA: Esta venta descuenta stock directamente
     */
    private static function crearMovimientoVenta(Venta $venta, DetalleVenta $detalle): void
    {
        $bodegaId = $venta->bodega_id;
        $productoId = $detalle->producto_id;
        $cantidad = (float) $detalle->cantidad;
        $lote = $detalle->lote;
        $vence = $detalle->fecha_vencimiento;
        $serial = $detalle->serial;

        // Validar cantidad
        StockService::validateQuantity($cantidad);

        // Validar stock disponible
        StockService::validateStock($productoId, $bodegaId, $cantidad, $lote, $vence);

        // Verificar si ya existe movimiento para este detalle
        if (MovimientoInventario::where('documento_tipo', 'venta')
            ->where('documento_id', $venta->id)
            ->where('detalle_venta_id', $detalle->id)
            ->exists()
        ) {
            throw new InvalidArgumentException(
                "Ya existe movimiento registrado para este detalle de venta (ID: {$detalle->id})"
            );
        }

        $producto = $detalle->producto;
        $serialModel = null;

        if ($producto && $producto->usaSeriales()) {
            if ((float) $cantidad !== 1.0) {
                throw new InvalidArgumentException('Los productos controlados por serial deben venderse con cantidad 1 por línea.');
            }

            $serial = trim((string) $serial);
            if ($serial === '') {
                throw new InvalidArgumentException('Debe indicar un número de serie para el producto controlado por serial.');
            }

            $serialModel = StockBodegaSerial::where('serial', $serial)
                ->where('status', 'available')
                ->first();

            if (! $serialModel) {
                throw new InvalidArgumentException("El número de serie '{$serial}' no está disponible en inventario.");
            }
        }

        // 1. Actualizar Stock (DESCUENTA porque venta directa)
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
            $stockLote->decrement('cantidad', $cantidad);
            $stockLote->refresh();
            $stockResult = $stockLote->cantidad;
        } else {
            $stock = StockBodega::firstOrCreate(
                ['producto_id' => $productoId, 'bodega_id' => $bodegaId],
                ['cantidad' => 0]
            );
            $stock->decrement('cantidad', $cantidad);
            $stock->refresh();
            $stockResult = $stock->cantidad;
        }

        if ($serialModel) {
            $serialModel->update(['status' => 'sold']);
        }

        // 2. Registrar Movimiento (costo_unitario = costo promedio ponderado, req. 8)
        $costoProducto = $detalle->producto
            ? (float) ($detalle->producto->costo_promedio ?? $detalle->producto->precio_compra)
            : 0;

        // Guardar el costo en el detalle de venta para cálculo de utilidades
        $detalle->update(['costo_unitario' => $costoProducto]);

        MovimientoInventario::create([
            'producto_id' => $productoId,
            'bodega_id' => $bodegaId,
            'tipo_movimiento' => 'salida_venta',
            'cantidad' => $cantidad,
            'costo_unitario' => $costoProducto,
            'lote' => $lote,
            'fecha_vencimiento' => $vence,
            'stock_resultante' => $stockResult,
            'documento_tipo' => 'venta',
            'documento_id' => $venta->id,
            'detalle_venta_id' => $detalle->id,
            'observacion' => "Salida por Venta #{$venta->numero}",
            'usuario_id' => Auth::id() ?? $venta->usuario_id,
            'fecha_movimiento' => now(),
        ]);
    }

    /**
     * Registra movimiento de venta vinculada a remisión
     * NOTA: NO descuenta stock (remisión ya lo hizo)
     * Solo registra como referencia de facturación
     */
    private static function registrarMovimientoVentaDesdeRemision(Venta $venta, DetalleVenta $detalle): void
    {
        $bodegaId = $venta->bodega_id;
        $productoId = $detalle->producto_id;
        $cantidad = $detalle->cantidad;

        // Obtener stock actual sin modificar
        $stockResult = StockService::getAvailableStock($productoId, $bodegaId);
        $costoProducto = $detalle->producto
            ? (float) ($detalle->producto->costo_promedio ?? $detalle->producto->precio_compra)
            : 0;

        // Registrar movimiento solo como referencia (tipo diferente)
        MovimientoInventario::create([
            'producto_id' => $productoId,
            'bodega_id' => $bodegaId,
            'tipo_movimiento' => 'facturacion_remision',  // Tipo especial: no afecta stock
            'cantidad' => $cantidad,
            'costo_unitario' => $costoProducto,
            'stock_resultante' => $stockResult,
            'documento_tipo' => 'venta',
            'documento_id' => $venta->id,
            'detalle_venta_id' => $detalle->id,
            'observacion' => "Facturación de Remisión #{$venta->remision?->numero} como Venta #{$venta->numero}",
            'usuario_id' => Auth::id() ?? $venta->usuario_id,
            'fecha_movimiento' => now(),
        ]);
    }

    /**
     * Cambia estado de venta a pagada
     */
    public static function marcarPagada(Venta $venta): void
    {
        if (! in_array(VentaEstado::PAGADA, $venta->estado->validTransitions())) {
            throw new InvalidArgumentException(
                "No se puede marcar como pagada una venta en estado {$venta->estado->label()}. Transición no permitida."
            );
        }

        // Si está en borrador, confirmar primero
        if ($venta->estado?->value === VentaEstado::BORRADOR->value) {
            static::confirmar($venta);
        }

        $estadoAnterior = $venta->estado;
        $venta->update([
            'estado' => VentaEstado::PAGADA,
            'estado_pago' => 'pagado',
        ]);

        // Registrar en auditoría
        static::registrarAuditoria(
            documentoTipo: 'venta',
            documentoId: $venta->id,
            accion: 'mark_paid',
            campo: 'estado',
            valorAnterior: $estadoAnterior->value,
            valorNuevo: VentaEstado::PAGADA->value,
            estadoDocumento: VentaEstado::PAGADA->value,
            observacion: 'Venta marcada como pagada.',
        );
    }

    /**
     * Anula una venta
     *
     * Comportamiento:
     * - Si venta SIN remisión: genera movimientos inversos (recupera stock)
     * - Si venta CON remisión: solo registra como anulación, remisión vuelve a CONFIRMADA
     */
    public static function anular(Venta $venta, ?string $razon = null): void
    {
        // Recargar estado desde DB para evitar estado stale
        $venta = Venta::lockForUpdate()->find($venta->id) ?? $venta;

        if (! in_array(VentaEstado::ANULADA, $venta->estado->validTransitions())) {
            throw new InvalidArgumentException(
                "No se puede anular una venta en estado {$venta->estado->label()}. Transición no permitida."
            );
        }

        DB::beginTransaction();

        try {
            // Si está confirmada, manejar según si tiene remisión vinculada
            if ($venta->estado?->value === VentaEstado::CONFIRMADA->value) {
                if ($venta->remision_id === null) {
                    // Venta directa: revertir movimientos (recuperar stock)
                    foreach ($venta->detalles as $detalle) {
                        static::revertirMovimientoVenta($venta, $detalle);
                    }
                } else {
                    // Venta vinculada a remisión: solo registrar anulación, remisión vuelve a CONFIRMADA
                    foreach ($venta->detalles as $detalle) {
                        static::registrarAnulacionVentaDesdeRemision($venta, $detalle);
                    }

                    // Remisión vuelve a CONFIRMADA (se anula la venta, no la remisión)
                    $venta->remision?->update(['estado' => RemisionEstado::CONFIRMADA]);
                }
            }

            $estadoAnterior = $venta->estado;
            $venta->update([
                'estado' => VentaEstado::ANULADA,
                'estado_pago' => 'anulada',
            ]);

            // Registrar en auditoría
            static::registrarAuditoria(
                documentoTipo: 'venta',
                documentoId: $venta->id,
                accion: 'cancel',
                campo: 'estado',
                valorAnterior: $estadoAnterior->value,
                valorNuevo: VentaEstado::ANULADA->value,
                estadoDocumento: VentaEstado::ANULADA->value,
                observacion: $razon ?? 'Venta anulada sin especificar razón.',
            );

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Revierte movimiento de inventario (anulación de venta directa)
     * NOTA: Solo se llama si venta NO tiene remisión vinculada
     */
    private static function revertirMovimientoVenta(Venta $venta, DetalleVenta $detalle): void
    {
        $bodegaId = $venta->bodega_id;
        $productoId = $detalle->producto_id;
        $cantidad = $detalle->cantidad;
        $lote = $detalle->lote;
        $vence = $detalle->fecha_vencimiento;
        $serial = $detalle->serial;

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
                $stockLote->increment('cantidad', $cantidad);
                $stockLote->refresh();
            }
            $stockResult = $stockLote ? $stockLote->cantidad : 0;
        } else {
            $stock = StockBodega::where('producto_id', $productoId)
                ->where('bodega_id', $bodegaId)
                ->first();

            if ($stock) {
                $stock->increment('cantidad', $cantidad);
                $stock->refresh();
            }
            $stockResult = $stock ? $stock->cantidad : 0;
        }

        // Revertir serial si es necesario
        if ($serial) {
            $serialModel = StockBodegaSerial::where('serial', $serial)->first();
            if ($serialModel) {
                $serialModel->update(['status' => 'available']);
            }
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
            'documento_tipo' => 'venta',
            'documento_id' => $venta->id,
            'detalle_venta_id' => $detalle->id,
            'observacion' => "Anulación de Venta #{$venta->numero}",
            'usuario_id' => Auth::id() ?? $venta->usuario_id,
            'fecha_movimiento' => now(),
        ]);
    }

    /**
     * Notifica a administradores y auxiliares si algún producto quedó con stock bajo tras la venta
     */
    private static function notificarStockBajo(Venta $venta): void
    {
        try {
            $destinatarios = User::role(['administrador', 'auxiliar'])->get();
            if ($destinatarios->isEmpty()) {
                return;
            }

            foreach ($venta->detalles as $detalle) {
                $producto = $detalle->producto;
                if (! $producto || ! $producto->activo || $producto->stock_minimo <= 0) {
                    continue;
                }

                $stockActual = StockBodega::where('producto_id', $producto->id)
                    ->where('bodega_id', $venta->bodega_id)
                    ->value('cantidad') ?? 0;

                if ($stockActual <= $producto->stock_minimo) {
                    $bodegaNombre = $venta->bodega?->nombre ?? 'Principal';
                    $nivel = $stockActual <= 0 ? 'agotado' : 'bajo';

                    Notification::send($destinatarios, new StockBajoNotification(
                        $producto,
                        (float) $stockActual,
                        $bodegaNombre,
                        $nivel,
                    ));
                }
            }
        } catch (\Throwable $e) {
            // No bloquear la venta si falla la notificación
            Log::warning("Error al notificar stock bajo: {$e->getMessage()}");
        }
    }

    /**
     * Crea una venta desde el Punto de Venta (POS).
     *
     * A diferencia de confirmar():
     *  - La venta se crea y confirma en un solo paso atómico.
     *  - Requiere un turno POS activo (source of truth para caja_id y bodega_id).
     *  - El subtotal/impuestos/total se calculan en backend desde
     *    precio_venta + descuento_unitario del producto (no se confían
     *    en los precios enviados por el cliente).
     *  - Acepta pagos mixtos: el cliente envía un array de pagos por
     *    forma_pago_id. El efectivo puede sobrepagar (vuelto solo visual).
     *    Los no-efectivos NO pueden sobrepagar: su suma debe ser
     *    exactamente (total - efectivo_pagado).
     *  - Crea un PagoCliente por cada forma de pago, distribuyendo el
     *    monto en cascada hacia la venta recién creada.
     *  - Genera MovimientoCaja (ingreso) por cada pago si hay turno+caja.
     *  - Registra auditoría con accion='pos.crear'.
     *
     * @param  array{
     *     cliente_id:int,
     *     bodega_id?:int|null,
     *     descuento_global?:float,
     *     pagos?:array<int,array{forma_pago_id:int, monto:float}>,
     *     observaciones?:string|null,
     * }  $data
     * @param  array<int,array{producto_id:int, cantidad:float, descuento_unitario?:float}>  $items
     * @return array{id:int, numero:string, total:float, pagos:array<int,int>}
     */
    public static function crearPos(User $usuario, array $data, array $items): array
    {
        $clienteId = (int) ($data['cliente_id'] ?? 0);
        $bodegaId = (int) ($data['bodega_id'] ?? 0) ?: null;
        $descuentoGlobal = (float) ($data['descuento_global'] ?? 0);
        $pagos = $data['pagos'] ?? [];
        $observaciones = $data['observaciones'] ?? null;

        if ($clienteId <= 0) {
            throw new InvalidArgumentException('cliente_id es requerido.');
        }
        if (empty($items)) {
            throw new InvalidArgumentException('La venta debe tener al menos un item.');
        }

        $cliente = Cliente::find($clienteId);
        if (! $cliente || $cliente->estado !== ClienteEstado::ACTIVO) {
            throw new InvalidArgumentException('Cliente no encontrado o inactivo.');
        }

        // Turno activo es la fuente de verdad para caja/bodega
        $turno = Turno::where('usuario_id', $usuario->id)
            ->where('estado', TurnoEstado::ABIERTO)
            ->orderByDesc('id')
            ->first();

        if (! $turno) {
            throw new InvalidArgumentException(
                'No tienes un turno POS abierto. Abre caja antes de facturar.'
            );
        }

        if ($bodegaId === null && $turno->bodega_id) {
            $bodegaId = (int) $turno->bodega_id;
        }
        if ($bodegaId === null) {
            $bodegaId = (int) Empresa::getBodegaPrincipalId();
        }
        if ($bodegaId === null) {
            throw new InvalidArgumentException('No se pudo determinar la bodega para la venta.');
        }

        $bodega = Bodega::find($bodegaId);
        if (! $bodega) {
            throw new InvalidArgumentException('Bodega no encontrada.');
        }

        // Numeración con lock (reusa el consecutivo del sistema unificado)
        $numeracion = NumeracionService::obtenerSiguienteNumero('venta');

        return DB::transaction(function () use (
            $usuario, $cliente, $bodegaId, $descuentoGlobal, $pagos, $observaciones, $turno, $items, $numeracion
        ) {
            $productoIds = array_column($items, 'producto_id');
            $productos = Producto::with('impuesto')
                ->whereIn('id', $productoIds)
                ->where('activo', true)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // Validar cantidades, precios y stock antes de persistir nada
            $subtotal = 0.0;
            $impuestos = 0.0;
            $lineas = [];

            foreach ($items as $idx => $item) {
                $productoId = (int) ($item['producto_id'] ?? 0);
                $cantidad = (float) ($item['cantidad'] ?? 0);
                $descuentoItem = (float) ($item['descuento_unitario'] ?? 0);

                if ($productoId <= 0 || $cantidad <= 0) {
                    throw new InvalidArgumentException("Item #{$idx} inválido: producto_id y cantidad son requeridos.");
                }

                $producto = $productos->get($productoId);
                if (! $producto) {
                    throw new InvalidArgumentException("Producto ID {$productoId} no encontrado o inactivo.");
                }

                StockService::validateStock($productoId, $bodegaId, $cantidad);

                $precioUnitario = (float) $producto->precio_venta;
                $porcentaje = $producto->impuesto
                    ? (float) $producto->impuesto->porcentaje
                    : 0.0;

                $base = round($cantidad * $precioUnitario - $descuentoItem, 2);
                if ($base < 0) {
                    $base = 0.0;
                }
                $iva = round($base * ($porcentaje / 100), 2);
                $lineaSubtotal = round($base + $iva, 2);

                $subtotal += $base;
                $impuestos += $iva;

                $lineas[] = [
                    'producto' => $producto,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precioUnitario,
                    'descuento_unitario' => $descuentoItem,
                    'impuesto_id' => $producto->impuesto_id,
                    'porcentaje' => $porcentaje,
                    'base' => $base,
                    'iva' => $iva,
                    'subtotal' => $lineaSubtotal,
                ];
            }

            $total = round($subtotal + $impuestos - $descuentoGlobal, 2);
            if ($total < 0) {
                $total = 0.0;
            }

            // Crear la Venta
            $esContado = ! empty($pagos);
            $estado = $esContado ? VentaEstado::PAGADA : VentaEstado::CONFIRMADA;
            $estadoPago = $esContado ? EstadoPagoEnum::PAGADO : EstadoPagoEnum::PENDIENTE;
            $saldoPendiente = $esContado ? 0.0 : $total;

            $venta = Venta::create([
                'numero' => $numeracion['numero'],
                'estado' => $estado,
                'cliente_id' => $cliente->id,
                'bodega_id' => $bodegaId,
                'usuario_id' => $usuario->id,
                'fecha' => now(),
                'subtotal' => $subtotal,
                'descuento' => $descuentoGlobal,
                'impuestos' => $impuestos,
                'total' => $total,
                'total_confirmado' => $total,
                'impuestos_confirmados' => $impuestos,
                'snapshot_confirmacion' => json_encode([
                    'subtotal' => $subtotal,
                    'descuento' => $descuentoGlobal,
                    'impuestos' => $impuestos,
                    'total' => $total,
                    'saldo_pendiente' => $saldoPendiente,
                    'origen' => 'pos',
                    'turno_id' => $turno->id,
                    'usuario_id' => $usuario->id,
                    'fecha_confirmacion' => now()->toIso8601String(),
                ]),
                'confirmada_en' => now(),
                'saldo_pendiente' => $saldoPendiente,
                'estado_pago' => $estadoPago,
                'observaciones' => $observaciones,
            ]);

            // VentaObserver::saving (saving hook) reasigna saldo_pendiente=total al crear si viene en 0.
            // Forzamos un update explícito: con $exists=true, la regla de PAGADO→0 sí aplica.
            if ($esContado) {
                $venta->update(['saldo_pendiente' => 0]);
            }

            // Detalles + descuento de stock + kardex
            foreach ($lineas as $linea) {
                DetalleVenta::create([
                    'venta_id' => $venta->id,
                    'producto_id' => $linea['producto']->id,
                    'cantidad' => $linea['cantidad'],
                    'precio_unitario' => $linea['precio_unitario'],
                    'descuento_unitario' => $linea['descuento_unitario'],
                    'impuesto_id' => $linea['impuesto_id'],
                    'subtotal' => $linea['subtotal'],
                    'costo_unitario' => (float) ($linea['producto']->costo_promedio ?: $linea['producto']->precio_compra),
                ]);

                $stock = StockBodega::firstOrCreate(
                    ['producto_id' => $linea['producto']->id, 'bodega_id' => $bodegaId],
                    ['cantidad' => 0]
                );
                $stock->decrement('cantidad', $linea['cantidad']);
                $stock->refresh();

                MovimientoInventario::create([
                    'producto_id' => $linea['producto']->id,
                    'bodega_id' => $bodegaId,
                    'tipo_movimiento' => 'salida_venta',
                    'cantidad' => $linea['cantidad'],
                    'costo_unitario' => (float) ($linea['producto']->costo_promedio ?: $linea['producto']->precio_compra),
                    'stock_resultante' => (float) $stock->cantidad,
                    'documento_tipo' => 'venta',
                    'documento_id' => $venta->id,
                    'observacion' => "Salida por Venta POS #{$venta->numero}",
                    'usuario_id' => $usuario->id,
                    'fecha_movimiento' => now(),
                ]);
            }

            // Cartera: débito (la venta genera saldo a favor del cliente)
            $cliente->increment('saldo', $total);

            $pagosRegistrados = [];

            if ($esContado) {
                // Regla de pago mixto (solo aplica si hay ≥2 formas de pago distintas):
                //  - suma_no_efectivo debe ser exactamente (total - suma_efectivo).
                //  - Si el cliente paga TODO en efectivo (con o sin vuelto), esta regla no aplica.
                $formasPago = FormaPago::whereIn('id', array_column($pagos, 'forma_pago_id'))->get()->keyBy('id');

                $esPagoEfectivoUnico = count($pagos) === 1 && strtolower((string) ($formasPago[$pagos[0]['forma_pago_id']]->nombre ?? '')) === 'efectivo';

                if (! $esPagoEfectivoUnico) {
                    $efectivo = collect($pagos)->filter(
                        fn ($p) => strtolower((string) ($formasPago[$p['forma_pago_id']]->nombre ?? '')) === 'efectivo'
                    )->sum(fn ($p) => (float) $p['monto']);

                    $noEfectivo = collect($pagos)->filter(
                        fn ($p) => strtolower((string) ($formasPago[$p['forma_pago_id']]->nombre ?? '')) !== 'efectivo'
                    )->sum(fn ($p) => (float) $p['monto']);

                    if ($noEfectivo > ($total - $efectivo) + 0.01) {
                        throw new InvalidArgumentException(
                            'Pago mixto inválido: la suma de pagos no-efectivo no puede exceder el déficit cubierto por efectivo. '
                            ."Total: {$total}, Efectivo: {$efectivo}, No-efectivo: {$noEfectivo}."
                        );
                    }
                }

                // Capear el monto de cada pago al total pendiente: el exceso en efectivo
                // es vuelto visual (no se persiste en PagoCliente ni MovimientoCaja).
                $restanteCubrir = $total;
                $totalPagado = 0.0;

                foreach ($pagos as $idx => $pago) {
                    $formaPagoId = (int) ($pago['forma_pago_id'] ?? 0);
                    $montoInput = (float) ($pago['monto'] ?? 0);
                    if ($formaPagoId <= 0 || $montoInput <= 0) {
                        throw new InvalidArgumentException("Pago #{$idx} inválido: forma_pago_id y monto son requeridos.");
                    }
                    if (! $formasPago->has($formaPagoId)) {
                        throw new InvalidArgumentException("Forma de pago #{$formaPagoId} no encontrada o inactiva.");
                    }

                    $montoAplicar = round(min($montoInput, $restanteCubrir), 2);
                    $restanteCubrir = round($restanteCubrir - $montoAplicar, 2);
                    if ($montoAplicar <= 0) {
                        // Pago completamente excedente (vuelto puro): se ignora del registro
                        continue;
                    }

                    $pagoCliente = PagoClienteService::crear([
                        'cliente_id' => $cliente->id,
                        'forma_pago_id' => $formaPagoId,
                        'caja_id' => $turno->caja_id,
                        'fecha' => now(),
                        'monto' => $montoAplicar,
                        'referencia' => $venta->numero.'-'.$formaPagoId,
                        'observaciones' => 'Pago POS Venta #'.$venta->numero,
                    ]);

                    $totalPagado += $montoAplicar;
                    $pagosRegistrados[$formaPagoId] = ($pagosRegistrados[$formaPagoId] ?? 0) + $montoAplicar;
                }

                // Si quedó saldo pendiente y se cubrió parcialmente, venta a PARCIAL
                $saldoResidual = round($total - $totalPagado, 2);
                if ($saldoResidual > 0.01) {
                    $venta->update([
                        'estado_pago' => EstadoPagoEnum::PARCIAL,
                        'saldo_pendiente' => $saldoResidual,
                    ]);
                }

                // Cartera: crédito por lo efectivamente recibido (capeado al total)
                $cliente->decrement('saldo', min($totalPagado, $total));
            }

            // Auditoría
            static::registrarAuditoria(
                documentoTipo: 'venta',
                documentoId: $venta->id,
                accion: 'pos.crear',
                campo: 'estado',
                valorAnterior: null,
                valorNuevo: $estado->value,
                estadoDocumento: $estado->value,
                observacion: "Venta POS #{$venta->numero} creada. Total: {$total}. Turno: #{$turno->id}.",
            );

            // Notificar stock bajo fuera de la transacción
            DB::afterCommit(function () use ($venta) {
                static::notificarStockBajo($venta);
            });

            return [
                'id' => $venta->id,
                'numero' => $venta->numero,
                'total' => (float) $venta->total,
                'pagos' => $pagosRegistrados,
            ];
        });
    }

    /**
     * Registra anulación de venta vinculada a remisión
     * NOTA: NO revierte stock (remisión mantiene su descuento)
     * Solo registra para auditoría
     */
    private static function registrarAnulacionVentaDesdeRemision(Venta $venta, DetalleVenta $detalle): void
    {
        $bodegaId = $venta->bodega_id;
        $productoId = $detalle->producto_id;
        $cantidad = $detalle->cantidad;

        // Obtener stock actual sin modificar
        $stockResult = StockService::getAvailableStock($productoId, $bodegaId);
        $costoProducto = $detalle->producto
            ? (float) ($detalle->producto->costo_promedio ?? $detalle->producto->precio_compra)
            : 0;

        // Registrar anulación solo como referencia (sin afectar stock)
        MovimientoInventario::create([
            'producto_id' => $productoId,
            'bodega_id' => $bodegaId,
            'tipo_movimiento' => 'anulacion_venta_remision',  // Tipo especial: no afecta stock
            'cantidad' => $cantidad,
            'costo_unitario' => $costoProducto,
            'stock_resultante' => $stockResult,
            'documento_tipo' => 'venta',
            'documento_id' => $venta->id,
            'detalle_venta_id' => $detalle->id,
            'observacion' => "Anulación de Venta #{$venta->numero} (vinculada a Remisión #{$venta->remision?->numero})",
            'usuario_id' => Auth::id() ?? $venta->usuario_id,
            'fecha_movimiento' => now(),
        ]);
    }
}
