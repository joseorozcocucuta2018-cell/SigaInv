<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\CotizacionEstado;
use App\Models\Cotizacion;
use App\Services\NumeracionService;
use App\Traits\RegistraAuditoria;
use Illuminate\Support\Facades\Auth;

class CotizacionObserver
{
    use RegistraAuditoria;

    public function creating(Cotizacion $cotizacion): void
    {
        if (empty($cotizacion->numero)) {
            try {
                $resultado = NumeracionService::obtenerSiguienteNumero('cotizacion');
                $cotizacion->numero = $resultado['numero'];
            } catch (\Exception $e) {
                throw $e;
            }
        }

        if (Auth::check() && ! $cotizacion->usuario_id) {
            $cotizacion->usuario_id = Auth::id();
        }
    }

    /**
     * Registra cambios de campos en cotizaciones
     */
    public function updated(Cotizacion $cotizacion): void
    {
        $dirty = $cotizacion->getDirty();
        $original = $cotizacion->getOriginal();

        // Campos que no se auditan (internos)
        $excluir = ['updated_at', 'usuario_id'];

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
                documentoTipo: 'cotizacion',
                documentoId: $cotizacion->id,
                accion: 'update',
                campo: $campo,
                valorAnterior: $valorAnterior,
                valorNuevo: $valorNuevo,
                estadoDocumento: $cotizacion->estado?->value ?? 'pendiente',
                observacion: "Actualización de campo: {$campo}",
            );
        }
    }

    /**
     * Cuando se crea una Venta o Remisión que referencia esta cotización,
     * marca la cotización como "aceptada".
     *
     * Nota: Este evento se dispara desde VentaObserver/RemisionObserver
     * cuando detectan que tienen cotizacion_id.
     */
    public function markAsAccepted(Cotizacion $cotizacion): void
    {
        if ($cotizacion->estado !== CotizacionEstado::ACEPTADA) {
            $cotizacion->update(['estado' => CotizacionEstado::ACEPTADA]);
        }
    }

    /**
     * Verifica si la cotización es válida antes de usarla en una venta/remisión.
     * Valida:
     * - Estado debe ser 'pendiente', 'enviada' o 'aceptada' (no rechazada ni vencida)
     * - Fecha de vigencia debe ser >= hoy (si está definida)
     *
     * @throws \InvalidArgumentException
     */
    public static function validateForUse(Cotizacion $cotizacion): bool
    {
        if ($cotizacion->estado?->value === CotizacionEstado::RECHAZADA->value) {
            throw new \InvalidArgumentException(
                "No se puede usar una cotización rechazada (ID: {$cotizacion->id})"
            );
        }

        if ($cotizacion->estado?->value === CotizacionEstado::VENCIDA->value) {
            throw new \InvalidArgumentException(
                "La cotización ha vencido (ID: {$cotizacion->id})"
            );
        }

        if ($cotizacion->fecha_vigencia && $cotizacion->fecha_vigencia < now()->toDateString()) {
            throw new \InvalidArgumentException(
                "La cotización ha expirado (vigencia: {$cotizacion->fecha_vigencia}, hoy: ".now()->toDateString().')'
            );
        }

        return true;
    }
}
