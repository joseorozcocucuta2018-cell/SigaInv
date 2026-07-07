<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\CotizacionEstado;
use App\Models\Cotizacion;
use App\Traits\RegistraAuditoria;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class CotizacionService
{
    use RegistraAuditoria;

    public const TRANSICIONES = [
        'pendiente' => ['enviada', 'rechazada', 'vencida'],
        'enviada' => ['rechazada', 'vencida'],
        'aceptada' => [],
        'rechazada' => [],
        'vencida' => [],
    ];

    public static function cambiarEstado(Cotizacion $cotizacion, CotizacionEstado $nuevoEstado): void
    {
        $estadoActual = $cotizacion->estado->value;
        $permitidos = self::TRANSICIONES[$estadoActual] ?? [];

        if (! in_array($nuevoEstado->value, $permitidos, true)) {
            throw new InvalidArgumentException(
                "No se puede cambiar el estado de '{$estadoActual}' a '{$nuevoEstado->value}'."
            );
        }

        $cotizacion->update(['estado' => $nuevoEstado]);

        static::registrarAuditoria(
            documentoTipo: 'cotizacion',
            documentoId: $cotizacion->id,
            accion: 'estado',
            campo: 'estado',
            valorAnterior: $estadoActual,
            valorNuevo: $nuevoEstado->value,
            estadoDocumento: $nuevoEstado->value,
        );

        // Al cambiar a ENVIADA, enviar PDF por email al cliente (silencioso si no tiene email)
        if ($nuevoEstado === CotizacionEstado::ENVIADA) {
            try {
                app(DocumentoEmailService::class)->enviarDocumento($cotizacion, 'cotizacion');
            } catch (\Throwable $e) {
                Log::warning("No se pudo enviar email de cotización {$cotizacion->id}: ".$e->getMessage());
            }
        }
    }

    /**
     * Marca la cotización como aceptada. Solo uso interno del sistema
     * (cuando se crea una Venta o Remisión desde la cotización).
     */
    public static function marcarAceptada(Cotizacion $cotizacion): void
    {
        if ($cotizacion->estado?->value === CotizacionEstado::ACEPTADA->value) {
            return;
        }
        $cotizacion->update(['estado' => CotizacionEstado::ACEPTADA]);
    }

    public static function verificarVencimiento(Cotizacion $cotizacion): void
    {
        if (
            in_array($cotizacion->estado?->value, [CotizacionEstado::PENDIENTE->value, CotizacionEstado::ENVIADA->value])
            && $cotizacion->fecha_vigencia !== null
            && $cotizacion->fecha_vigencia < now()->toDateString()
        ) {
            $cotizacion->update(['estado' => CotizacionEstado::VENCIDA]);
        }
    }
}
