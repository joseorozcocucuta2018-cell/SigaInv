<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\EstadoPagoEnum;
use App\Models\Venta;
use App\Services\NumeracionService;
use App\Traits\RegistraAuditoria;
use Illuminate\Support\Facades\Auth;

class VentaObserver
{
    use RegistraAuditoria;

    /**
     * Registra cambios de campos en ventas (solo en estado borrador)
     */
    public function updated(Venta $venta): void
    {
        $dirty = $venta->getDirty();
        $original = $venta->getOriginal();

        // Campos que no se auditan (calculados o internos)
        $excluir = ['updated_at', 'saldo_pendiente', 'confirmada_en', 'usuario_id'];

        foreach ($dirty as $campo => $valorNuevo) {
            if (in_array($campo, $excluir)) {
                continue;
            }
            if (! array_key_exists($campo, $original)) {
                continue;
            }
            $valorAnterior = $original[$campo];
            if ($valorAnterior === $valorNuevo) {
                continue;
            }

            static::registrarAuditoria(
                documentoTipo: 'venta',
                documentoId: $venta->id,
                accion: 'update',
                campo: $campo,
                valorAnterior: $valorAnterior,
                valorNuevo: $valorNuevo,
                estadoDocumento: $venta->estado->value,
                observacion: "Actualización de campo: {$campo}",
            );
        }
    }

    public function creating(Venta $venta): void
    {
        if (empty($venta->numero)) {
            try {
                $resultado = NumeracionService::obtenerSiguienteNumero('venta');
                $venta->numero = $resultado['numero'];
            } catch (\Exception $e) {
                // Si no hay numeración configurada, generamos un número temporal o lanzamos error
                // Para ventas (legal), es mejor lanzar el error
                throw $e;
            }
        }

        if (Auth::check() && ! $venta->usuario_id) {
            $venta->usuario_id = Auth::id();
        }
    }

    public function saving(Venta $venta): void
    {
        if ($venta->estado_pago === EstadoPagoEnum::PAGADO && $venta->saldo_pendiente > 0) {
            $venta->saldo_pendiente = 0;
        }

        if (! $venta->exists && (float) $venta->total > 0 && (float) $venta->saldo_pendiente == 0) {
            $venta->saldo_pendiente = $venta->total;
        }
    }
}
