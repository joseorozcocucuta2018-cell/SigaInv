<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ClienteEstado;
use App\Enums\EstadoPagoEnum;
use App\Enums\RemisionEstado;
use App\Enums\TurnoEstado;
use App\Enums\VentaEstado;
use App\Models\Bodega;
use App\Models\Cliente;
use App\Models\DetalleRemision;
use App\Models\Empresa;
use App\Models\FormaPago;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\Remision;
use App\Models\StockBodega;
use App\Models\StockBodegaLote;
use App\Models\StockBodegaSerial;
use App\Models\Turno;
use App\Models\User;
use App\Notifications\RemisionConfirmadaNotification;
use App\Notifications\StockBajoNotification;
use App\Observers\CotizacionObserver;
use App\Traits\LoggingCriticoInventario;
use App\Traits\RegistraAuditoria;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use InvalidArgumentException;

/**
 * Servicio para operaciones de Remisión
 * Maneja confirmación y cambios de estado
 */
class RemisionService
{
    use LoggingCriticoInventario, RegistraAuditoria;

    /**
     * Confirma una remisión y genera movimientos de inventario
     *
     * @throws InvalidArgumentException Si no se puede confirmar
     */
    public static function confirmar(Remision $remision): void
    {
        if (! in_array(RemisionEstado::CONFIRMADA, $remision->estado->validTransitions())) {
            throw new InvalidArgumentException(
                "No se puede confirmar una remisión en estado {$remision->estado->label()}. Transición no permitida."
            );
        }

        // Validar totales (req. 10)
        if ((float) $remision->total < 0) {
            throw new InvalidArgumentException('El total de la remisión no puede ser negativo.');
        }

        if ((float) $remision->descuento > (float) $remision->subtotal) {
            throw new InvalidArgumentException(
                'El descuento no puede ser mayor que el subtotal.'
            );
        }

        DB::beginTransaction();

        try {
            // Bloquear fila para evitar doble confirmación concurrente (req. 4)
            $remision = Remision::lockForUpdate()->find($remision->id);

            // Validar detalles y datos mínimos
            $remision->validarConfirmable();

            // Generar movimientos de inventario
            foreach ($remision->detalles as $detalle) {
                static::crearMovimientoRemision($remision, $detalle);
            }

            // Cambiar estado y registrar fecha de confirmación con snapshot
            $estadoAnterior = $remision->estado;
            $remision->confirmada_en = now();
            $remision->update([
                'estado' => RemisionEstado::CONFIRMADA,
                'total_confirmado' => $remision->total,
                'impuestos_confirmados' => $remision->impuestos,
                'snapshot_confirmacion' => json_encode([
                    'subtotal' => $remision->subtotal,
                    'descuento' => $remision->descuento,
                    'impuestos' => $remision->impuestos,
                    'total' => $remision->total,
                    'saldo_pendiente' => $remision->saldo_pendiente,
                    'usuario_id' => Auth::id(),
                    'fecha_confirmacion' => now()->toIso8601String(),
                ]),
            ]);

            // Registrar en auditoría
            static::registrarAuditoria(
                documentoTipo: 'remision',
                documentoId: $remision->id,
                accion: 'confirm',
                campo: 'estado',
                valorAnterior: $estadoAnterior->value,
                valorNuevo: RemisionEstado::CONFIRMADA->value,
                estadoDocumento: RemisionEstado::CONFIRMADA->value,
                observacion: 'Remisión confirmada. Se generaron movimientos de inventario.',
            );

            DB::commit();
            static::logConfirmacionExitosa('remision', $remision->id, $remision->detalles->toArray());

            if ($remision->cotizacion_id) {
                $cotizacion = $remision->cotizacion;
                if ($cotizacion) {
                    CotizacionObserver::validateForUse($cotizacion);
                    CotizacionService::marcarAceptada($cotizacion);
                }
            }

            // Incrementar saldo del cliente (deuda)
            $remision->cliente?->increment('saldo', $remision->total);

            // Enviar PDF por email al cliente (silencioso si no tiene email)
            try {
                app(DocumentoEmailService::class)->enviarDocumento($remision, 'remision');
            } catch (\Throwable $e) {
                Log::warning("No se pudo enviar email de remisión {$remision->id}: ".$e->getMessage());
            }

            // Notificar stock bajo (fuera de la transacción)
            static::notificarStockBajo($remision);

            // Notificar a administradores (fuera de la transacción)
            try {
                $users = User::role('administrador')->get();
                Notification::send($users, new RemisionConfirmadaNotification($remision));
            } catch (\Exception) {
                // No bloquear la confirmación si la notificación falla
            }
        } catch (\Exception $e) {
            DB::rollBack();
            static::logConfirmacionFallida('remision', $remision->id ?? 0, $e);
            throw $e;
        }
    }

    /**
     * Crea movimiento de inventario para un detalle de remisión
     */
    private static function crearMovimientoRemision(Remision $remision, DetalleRemision $detalle): void
    {
        $bodegaId = $remision->bodega_id;
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
        if (MovimientoInventario::where('documento_tipo', 'remision')
            ->where('documento_id', $remision->id)
            ->where('detalle_remision_id', $detalle->id)
            ->exists()) {
            throw new InvalidArgumentException(
                "Ya existe movimiento registrado para este detalle de remisión (ID: {$detalle->id})"
            );
        }

        $producto = $detalle->producto;
        $serialModel = null;

        if ($producto && $producto->usaSeriales()) {
            if ((float) $cantidad !== 1.0) {
                throw new InvalidArgumentException('Los productos controlados por serial deben remitirse con cantidad 1 por línea.');
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
            $serialModel->update(['status' => 'reserved']);
        }

        // 2. Registrar Movimiento
        $costoProducto = $detalle->producto
            ? (float) ($detalle->producto->costo_promedio ?? $detalle->producto->precio_compra)
            : 0;

        $detalle->update(['costo_unitario' => $costoProducto]);

        MovimientoInventario::create([
            'producto_id' => $productoId,
            'bodega_id' => $bodegaId,
            'tipo_movimiento' => 'salida_remision',
            'cantidad' => $cantidad,
            'costo_unitario' => $costoProducto,
            'lote' => $lote,
            'fecha_vencimiento' => $vence,
            'stock_resultante' => $stockResult,
            'documento_tipo' => 'remision',
            'documento_id' => $remision->id,
            'detalle_remision_id' => $detalle->id,
            'observacion' => "Remisión #{$remision->numero}",
            'usuario_id' => Auth::id() ?? $remision->usuario_id,
            'fecha_movimiento' => now(),
        ]);
    }

    /**
     * Marca remisión como facturada
     */
    public static function marcarFacturada(Remision $remision): void
    {
        if ($remision->estado !== RemisionEstado::CONFIRMADA) {
            throw new InvalidArgumentException(
                "No se puede facturar una remisión en estado {$remision->estado->label()}"
            );
        }

        $remision->update([
            'estado' => RemisionEstado::FACTURADA,
        ]);
    }

    /**
     * Anula una remisión
     *
     * Restricción: No se puede anular si tiene venta confirmada/pagada vinculada
     * Genera movimientos inversos si está confirmada
     */
    public static function anular(Remision $remision, ?string $razon = null): void
    {
        // Recargar estado desde DB para evitar estado stale
        $remision = Remision::lockForUpdate()->find($remision->id) ?? $remision;

        if (! in_array(RemisionEstado::ANULADA, $remision->estado->validTransitions())) {
            throw new InvalidArgumentException(
                "No se puede anular una remisión en estado {$remision->estado->label()}. Transición no permitida."
            );
        }

        // Validar que no haya venta confirmada/pagada vinculada
        $ventaConfirmada = $remision->ventas()
            ->whereIn('estado', [VentaEstado::CONFIRMADA->value, VentaEstado::PAGADA->value])
            ->exists();

        if ($ventaConfirmada) {
            throw new InvalidArgumentException(
                'No se puede anular una remisión que tiene ventas confirmadas o pagadas vinculadas. '
                .'Primero debe anular las ventas.'
            );
        }

        DB::beginTransaction();

        try {
            // Si está confirmada, revertir movimientos
            if ($remision->estado?->value === RemisionEstado::CONFIRMADA->value) {
                foreach ($remision->detalles as $detalle) {
                    static::revertirMovimientoRemision($remision, $detalle);
                }
            }

            $estadoAnterior = $remision->estado;
            $remision->update([
                'estado' => RemisionEstado::ANULADA,
                'estado_pago' => 'anulada',
            ]);

            static::registrarAuditoria(
                documentoTipo: 'remision',
                documentoId: $remision->id,
                accion: 'anular',
                campo: 'estado',
                valorAnterior: $estadoAnterior->value,
                valorNuevo: RemisionEstado::ANULADA->value,
                estadoDocumento: RemisionEstado::ANULADA->value,
                observacion: $razon ? "Remisión anulada. Razon: {$razon}" : 'Remisión anulada. Se revirtieron movimientos de inventario.',
            );

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Revierte movimiento de inventario (anulación)
     */
    private static function revertirMovimientoRemision(Remision $remision, DetalleRemision $detalle): void
    {
        $bodegaId = $remision->bodega_id;
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
            'documento_tipo' => 'remision',
            'documento_id' => $remision->id,
            'detalle_remision_id' => $detalle->id,
            'observacion' => "Anulación de Remisión #{$remision->numero}",
            'usuario_id' => Auth::id() ?? $remision->usuario_id,
            'fecha_movimiento' => now(),
        ]);
    }

    /**
     * Notifica stock bajo tras confirmar remisión
     */
    private static function notificarStockBajo(Remision $remision): void
    {
        try {
            $destinatarios = User::role(['administrador', 'auxiliar'])->get();
            if ($destinatarios->isEmpty()) {
                return;
            }

            foreach ($remision->detalles as $detalle) {
                $producto = $detalle->producto;
                if (! $producto || ! $producto->activo || $producto->stock_minimo <= 0) {
                    continue;
                }

                $stockActual = StockBodega::where('producto_id', $producto->id)
                    ->where('bodega_id', $remision->bodega_id)
                    ->value('cantidad') ?? 0;

                if ($stockActual <= $producto->stock_minimo) {
                    $bodegaNombre = $remision->bodega?->nombre ?? 'Principal';
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
            Log::warning("Error al notificar stock bajo en remisión: {$e->getMessage()}");
        }
    }

    /**
     * Crea una remisión desde el Punto de Venta (POS).
     *
     * A diferencia de confirmar():
     *  - La remisión se crea y confirma en un solo paso atómico.
     *  - Requiere un turno POS activo.
     *  - Los totales se calculan en backend (subtotal = sum(cant*precio - desc)).
     *  - SIN IVA (impuestos = 0): regla inquebrantable del POS.
     *  - Acepta pagos mixtos con la misma regla: efectivo puede sobrepagar
     *    (vuelto visual), no-efectivo no puede exceder (total - efectivo).
     *  - Crea PagoCliente por cada forma de pago.
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
            throw new InvalidArgumentException('La remisión debe tener al menos un item.');
        }

        $cliente = Cliente::find($clienteId);
        if (! $cliente || $cliente->estado !== ClienteEstado::ACTIVO) {
            throw new InvalidArgumentException('Cliente no encontrado o inactivo.');
        }

        $turno = Turno::where('usuario_id', $usuario->id)
            ->where('estado', TurnoEstado::ABIERTO)
            ->orderByDesc('id')
            ->first();

        if (! $turno) {
            throw new InvalidArgumentException(
                'No tienes un turno POS abierto. Abre caja antes de remitir.'
            );
        }

        if ($bodegaId === null && $turno->bodega_id) {
            $bodegaId = (int) $turno->bodega_id;
        }
        if ($bodegaId === null) {
            $bodegaId = (int) Empresa::getBodegaPrincipalId();
        }
        if ($bodegaId === null) {
            throw new InvalidArgumentException('No se pudo determinar la bodega para la remisión.');
        }

        $bodega = Bodega::find($bodegaId);
        if (! $bodega) {
            throw new InvalidArgumentException('Bodega no encontrada.');
        }

        $numeracion = NumeracionService::obtenerSiguienteNumero('remision');

        return DB::transaction(function () use (
            $usuario, $cliente, $bodegaId, $descuentoGlobal, $pagos, $observaciones, $turno, $items, $numeracion
        ) {
            $productoIds = array_column($items, 'producto_id');
            $productos = Producto::whereIn('id', $productoIds)
                ->where('activo', true)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $subtotal = 0.0;
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
                $base = round($cantidad * $precioUnitario - $descuentoItem, 2);
                if ($base < 0) {
                    $base = 0.0;
                }
                // Remisión: SIN IVA. impuestos = 0 línea y global.
                $lineaSubtotal = round($base, 2);

                $subtotal += $base;

                $lineas[] = [
                    'producto' => $producto,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precioUnitario,
                    'descuento_unitario' => $descuentoItem,
                    'impuesto_id' => $producto->impuesto_id,
                    'subtotal' => $lineaSubtotal,
                ];
            }

            $impuestos = 0.0;
            $total = round($subtotal - $descuentoGlobal, 2);
            if ($total < 0) {
                $total = 0.0;
            }

            $esContado = ! empty($pagos);
            $estadoPago = $esContado ? EstadoPagoEnum::PAGADO : EstadoPagoEnum::PENDIENTE;
            $saldoPendiente = $esContado ? 0.0 : $total;

            $remision = Remision::create([
                'numero' => $numeracion['numero'],
                'estado' => RemisionEstado::CONFIRMADA,
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

            // RemisionObserver::saving reasigna saldo_pendiente=total al crear si viene en 0.
            if ($esContado) {
                $remision->update(['saldo_pendiente' => 0]);
            }

            foreach ($lineas as $linea) {
                DetalleRemision::create([
                    'remision_id' => $remision->id,
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
                    'tipo_movimiento' => 'salida_remision',
                    'cantidad' => $linea['cantidad'],
                    'costo_unitario' => (float) ($linea['producto']->costo_promedio ?: $linea['producto']->precio_compra),
                    'stock_resultante' => (float) $stock->cantidad,
                    'documento_tipo' => 'remision',
                    'documento_id' => $remision->id,
                    'observacion' => "Remisión POS #{$remision->numero}",
                    'usuario_id' => $usuario->id,
                    'fecha_movimiento' => now(),
                ]);
            }

            $cliente->increment('saldo', $total);

            $pagosRegistrados = [];

            if ($esContado) {
                // Regla de pago mixto (solo aplica si hay ≥2 formas de pago distintas).
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

                // Capear el monto de cada pago al total pendiente: el exceso en efectivo es vuelto visual.
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
                        continue;
                    }

                    PagoClienteService::crear([
                        'cliente_id' => $cliente->id,
                        'forma_pago_id' => $formaPagoId,
                        'caja_id' => $turno->caja_id,
                        'fecha' => now(),
                        'monto' => $montoAplicar,
                        'referencia' => $remision->numero.'-'.$formaPagoId,
                        'observaciones' => 'Pago POS Remisión #'.$remision->numero,
                    ]);

                    $totalPagado += $montoAplicar;
                    $pagosRegistrados[$formaPagoId] = ($pagosRegistrados[$formaPagoId] ?? 0) + $montoAplicar;
                }

                $saldoResidual = round($total - $totalPagado, 2);
                if ($saldoResidual > 0.01) {
                    $remision->update([
                        'estado_pago' => EstadoPagoEnum::PARCIAL,
                        'saldo_pendiente' => $saldoResidual,
                    ]);
                }

                $cliente->decrement('saldo', min($totalPagado, $total));
            }

            static::registrarAuditoria(
                documentoTipo: 'remision',
                documentoId: $remision->id,
                accion: 'pos.crear',
                campo: 'estado',
                valorAnterior: null,
                valorNuevo: RemisionEstado::CONFIRMADA->value,
                estadoDocumento: RemisionEstado::CONFIRMADA->value,
                observacion: "Remisión POS #{$remision->numero} creada. Total: {$total}. Turno: #{$turno->id}.",
            );

            DB::afterCommit(function () use ($remision) {
                static::notificarStockBajo($remision);
            });

            return [
                'id' => $remision->id,
                'numero' => $remision->numero,
                'total' => (float) $remision->total,
                'pagos' => $pagosRegistrados,
            ];
        });
    }
}
