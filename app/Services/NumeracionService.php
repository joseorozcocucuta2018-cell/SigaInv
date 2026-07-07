<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\NumeracionEstado;
use App\Models\Numeracion;

/**
 * Servicio para manejar la numeración automática de documentos
 *
 * Tipos de documento soportados:
 * - venta
 * - cotizacion
 * - remision
 * - compra (la numera el proveedor, pero guardamos interno si se requiere)
 * - pago_cliente
 * - pago_proveedor
 */
class NumeracionService
{
    /**
     * Obtiene el siguiente número para un tipo de documento
     * Usa bloqueo de fila para evitar concurrencia
     *
     * @param  string  $tipoDocumento  tipo de documento (venta, cotizacion, remision, etc.)
     * @return array ['numero' => string, 'numeracion' => Numeracion|null]
     *
     * @throws \Exception Si no hay numeración configurada para el tipo/año
     */
    public static function obtenerSiguienteNumero(string $tipoDocumento): array
    {
        $anno = now()->year;

        // Buscar numeración activa para el tipo y año
        $numeracion = Numeracion::where('tipo_documento', $tipoDocumento)
            ->where('anno', $anno)
            ->where('estado', NumeracionEstado::ACTIVO)
            ->lockForUpdate()
            ->first();

        if (! $numeracion) {
            throw new \Exception(
                "No hay numeración configurada para documentos tipo '{$tipoDocumento}' en el año {$anno}. "
                    .'Por favor configure la numeración en Configuración > Numeraciones.'
            );
        }

        // Validar que no haya excedido el rango
        if ($numeracion->consecutivo_actual >= $numeracion->consecutivo_hasta) {
            throw new \Exception(
                "Se ha agotado el rango de numeración para {$tipoDocumento}. "
                    ."Rango: {$numeracion->consecutivo_desde} - {$numeracion->consecutivo_hasta}"
            );
        }

        // Incrementar el consecutivo
        $nuevoConsecutivo = $numeracion->consecutivo_actual + 1;
        $numeracion->update(['consecutivo_actual' => $nuevoConsecutivo]);

        // Construir el número con prefijo y ceros a la izquierda
        $numero = $numeracion->prefijo.str_pad((string) $nuevoConsecutivo, 6, '0', STR_PAD_LEFT);

        return [
            'numero' => $numero,
            'numeracion' => $numeracion,
        ];
    }

    /**
     * Verifica si una numeración ya ha sido usada
     */
    public static function estaEnUso(Numeracion $numeracion): bool
    {
        return $numeracion->consecutivo_actual > 0;
    }

    /**
     * Obtiene el número actual (último usado)
     */
    public static function getNumeroActual(string $tipoDocumento, ?int $anno = null): ?string
    {
        $anno = $anno ?? now()->year;

        $numeracion = Numeracion::where('tipo_documento', $tipoDocumento)
            ->where('anno', $anno)
            ->first();

        if (! $numeracion || $numeracion->consecutivo_actual === 0) {
            return null;
        }

        return $numeracion->prefijo.str_pad($numeracion->consecutivo_actual, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Reinicia el consecutivo de una numeración (solo si no ha sido usada)
     *
     * @throws \Exception
     */
    public static function reiniciarConsecutivo(Numeracion $numeracion, int $nuevoConsecutivo): bool
    {
        if (self::estaEnUso($numeracion)) {
            throw new \Exception(
                'No se puede reiniciar el consecutivo porque ya hay documentos emitidos '
                    ."({$numeracion->consecutivo_actual} documentos)."
            );
        }

        if ($nuevoConsecutivo < $numeracion->consecutivo_desde) {
            throw new \Exception(
                "El nuevo consecutivo no puede ser menor al rango configurado (desde: {$numeracion->consecutivo_desde})."
            );
        }

        return $numeracion->update(['consecutivo_actual' => $nuevoConsecutivo - 1]);
    }

    /**
     * Verifica si existe numeración activa para un tipo de documento
     */
    public static function tieneNumeracionActiva(string $tipoDocumento, ?int $anno = null): bool
    {
        $anno = $anno ?? now()->year;

        return Numeracion::where('tipo_documento', $tipoDocumento)
            ->where('anno', $anno)
            ->where('estado', NumeracionEstado::ACTIVO)
            ->exists();
    }

    /**
     * Genera un número manual cuando no hay numeración configurada
     * Formato: REM-YYYYMMDD-HHmmss
     */
    public static function generarNumeroManual(string $tipoDocumento): string
    {
        $prefijo = match ($tipoDocumento) {
            'venta' => 'VEN',
            'cotizacion' => 'COT',
            'remision' => 'REM',
            default => strtoupper(substr($tipoDocumento, 0, 3)),
        };

        return $prefijo.'-'.now()->format('YmdHis');
    }
}
