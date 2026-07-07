<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\CompraEstado;
use App\Enums\RemisionEstado;
use App\Enums\VentaEstado;
use App\Models\Cliente;
use App\Models\Compra;
use App\Models\DetallePagoCliente;
use App\Models\DetallePagoProveedor;
use App\Models\PagoCliente;
use App\Models\PagoProveedor;
use App\Models\Proveedor;
use App\Models\Remision;
use App\Models\Venta;
use Illuminate\Support\Collection;

class PagoDistribucionService
{
    /**
     * Distribuir pago de cliente en cascada (waterfall).
     * Aplica el monto desde el documento más antiguo al más reciente.
     */
    public function distribuirPagoCliente(PagoCliente $pago): void
    {
        $montoRestante = (float) $pago->monto;

        // Obtener documentos pendientes del cliente, ordenados por fecha ASC
        $documentos = $this->obtenerDocumentosPendientesCliente($pago->cliente_id);

        foreach ($documentos as ['tipo' => $tipo, 'modelo' => $doc]) {
            if ($montoRestante <= 0) {
                break;
            }

            $saldo = (float) $doc->saldo_pendiente;
            $aplicar = min($montoRestante, $saldo);

            DetallePagoCliente::create([
                'pago_cliente_id' => $pago->id,
                'documento_tipo' => $tipo,
                'documento_id' => $doc->id,
                'monto_aplicado' => $aplicar,
            ]);

            $nuevoSaldo = $saldo - $aplicar;
            $nuevoEstado = $nuevoSaldo <= 0 ? 'pagado' : 'parcial';

            $doc->update([
                'saldo_pendiente' => max(0, $nuevoSaldo),
                'estado_pago' => $nuevoEstado,
            ]);

            $montoRestante -= $aplicar;
        }

        // Actualizar saldo del cliente: el pago reduce la deuda
        $pago->cliente->decrement('saldo', $pago->monto);
    }

    /**
     * Revertir distribución de pago de cliente.
     * Restaura saldo_pendiente y estado_pago de cada documento afectado.
     */
    public function revertirPagoCliente(PagoCliente $pago): void
    {
        foreach ($pago->detalles as $detalle) {
            $documento = $detalle->documento();
            if (! $documento) {
                continue;
            }

            $nuevoSaldo = (float) $documento->saldo_pendiente + (float) $detalle->monto_aplicado;
            $nuevoEstado = $nuevoSaldo >= (float) $documento->total ? 'pendiente' : 'parcial';

            $documento->update([
                'saldo_pendiente' => $nuevoSaldo,
                'estado_pago' => $nuevoEstado,
            ]);
        }

        // Revertir saldo del cliente: al eliminar un pago se restaura la deuda
        $pago->cliente->increment('saldo', $pago->monto);
    }

    /**
     * Distribuir pago a proveedor en cascada (waterfall).
     */
    public function distribuirPagoProveedor(PagoProveedor $pago): void
    {
        $montoRestante = (float) $pago->monto;

        $compras = Compra::where('proveedor_id', $pago->proveedor_id)
            ->whereIn('estado', [CompraEstado::REGISTRADA->value, CompraEstado::PENDIENTE->value])
            ->where('saldo_pendiente', '>', 0)
            ->orderBy('fecha', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        foreach ($compras as $compra) {
            if ($montoRestante <= 0) {
                break;
            }

            $saldo = (float) $compra->saldo_pendiente;
            $aplicar = min($montoRestante, $saldo);

            DetallePagoProveedor::create([
                'pago_proveedor_id' => $pago->id,
                'compra_id' => $compra->id,
                'monto_aplicado' => $aplicar,
            ]);

            $nuevoSaldo = $saldo - $aplicar;
            $nuevoEstado = $nuevoSaldo <= 0 ? CompraEstado::PAGADA : CompraEstado::PENDIENTE;

            $compra->update([
                'saldo_pendiente' => max(0, $nuevoSaldo),
                'estado' => $nuevoEstado,
            ]);

            $montoRestante -= $aplicar;
        }

        // Actualizar saldo del proveedor: el pago reduce la deuda
        $pago->proveedor->decrement('saldo', $pago->monto);
    }

    /**
     * Revertir distribución de pago a proveedor.
     */
    public function revertirPagoProveedor(PagoProveedor $pago): void
    {
        foreach ($pago->detalles as $detalle) {
            $compra = $detalle->compra;
            if (! $compra) {
                continue;
            }

            $nuevoSaldo = (float) $compra->saldo_pendiente + (float) $detalle->monto_aplicado;
            $nuevoEstado = $nuevoSaldo >= (float) $compra->total ? CompraEstado::REGISTRADA : CompraEstado::PENDIENTE;

            $compra->update([
                'saldo_pendiente' => $nuevoSaldo,
                'estado' => $nuevoEstado,
            ]);
        }

        // Revertir saldo del proveedor: al eliminar un pago se restaura la deuda
        $pago->proveedor->increment('saldo', $pago->monto);
    }

    /**
     * Obtener total de deuda pendiente de un cliente (ventas + remisiones).
     */
    public function obtenerDeudaCliente(int $clienteId): float
    {
        $deudaVentas = Venta::where('cliente_id', $clienteId)
            ->whereIn('estado_pago', ['pendiente', 'parcial'])
            ->sum('saldo_pendiente');

        $deudaRemisiones = Remision::where('cliente_id', $clienteId)
            ->whereIn('estado_pago', ['pendiente', 'parcial'])
            ->sum('saldo_pendiente');

        return (float) $deudaVentas + (float) $deudaRemisiones;
    }

    /**
     * Obtener total de deuda pendiente con un proveedor.
     */
    public function obtenerDeudaProveedor(int $proveedorId): float
    {
        return (float) Compra::where('proveedor_id', $proveedorId)
            ->whereIn('estado', [CompraEstado::REGISTRADA->value, CompraEstado::PENDIENTE->value])
            ->sum('saldo_pendiente');
    }

    /**
     * Obtener documentos pendientes de un cliente (ventas + remisiones)
     * ordenados por fecha ASC para distribución waterfall.
     */
    /**
     * Obtener documentos pendientes de un cliente (ventas + remisiones)
     * ordenados por fecha ASC para distribución waterfall.
     *
     * Retorna Collection de arrays ['tipo' => string, 'modelo' => Model]
     */
    public function obtenerDocumentosPendientesCliente(int $clienteId): Collection
    {
        $ventas = Venta::where('cliente_id', $clienteId)
            ->whereNotIn('estado', [VentaEstado::BORRADOR->value])
            ->whereIn('estado_pago', ['pendiente', 'parcial'])
            ->where('saldo_pendiente', '>', 0)
            ->get()
            ->map(fn ($v) => ['tipo' => 'venta', 'modelo' => $v]);

        $remisiones = Remision::where('cliente_id', $clienteId)
            ->whereNotIn('estado', [RemisionEstado::BORRADOR->value])
            ->whereIn('estado_pago', ['pendiente', 'parcial'])
            ->where('saldo_pendiente', '>', 0)
            ->get()
            ->map(fn ($r) => ['tipo' => 'remision', 'modelo' => $r]);

        return $ventas->concat($remisiones)
            ->sortBy(fn ($item) => $item['modelo']->fecha)
            ->values();
    }

    /**
     * Obtener compras pendientes de un proveedor.
     */
    public function obtenerComprasPendientesProveedor(int $proveedorId): Collection
    {
        return Compra::where('proveedor_id', $proveedorId)
            ->whereIn('estado', [CompraEstado::REGISTRADA->value, CompraEstado::PENDIENTE->value])
            ->where('saldo_pendiente', '>', 0)
            ->orderBy('fecha', 'asc')
            ->orderBy('id', 'asc')
            ->get();
    }
}
