<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Facades\Log;

/**
 * Registra operaciones críticas de inventario para auditoría y debugging
 *
 * Req. 13: Agregar logging crítico estructurado
 */
trait LoggingCriticoInventario
{
    /**
     * Log de confirmación exitosa
     */
    public static function logConfirmacionExitosa(string $tipoDocumento, int $documentoId, array $detalles = []): void
    {
        Log::channel('inventory')->info("Confirmación exitosa de {$tipoDocumento}", [
            'documento_id' => $documentoId,
            'tipo_documento' => $tipoDocumento,
            'usuario_id' => auth()->id(),
            'fecha' => now()->toIso8601String(),
            'detalles_count' => count($detalles),
        ]);
    }

    /**
     * Log de confirmación fallida
     */
    public static function logConfirmacionFallida(string $tipoDocumento, int $documentoId, \Exception $exception): void
    {
        Log::channel('inventory')->error("Confirmación fallida de {$tipoDocumento}", [
            'documento_id' => $documentoId,
            'tipo_documento' => $tipoDocumento,
            'usuario_id' => auth()->id(),
            'error' => $exception->getMessage(),
            'fecha' => now()->toIso8601String(),
        ]);
    }

    /**
     * Log de duplicación evitada
     */
    public static function logDuplicacionEvitada(string $tipoDocumento, int $documentoId): void
    {
        Log::channel('inventory')->warning('Intento de duplicación evitado', [
            'documento_id' => $documentoId,
            'tipo_documento' => $tipoDocumento,
            'usuario_id' => auth()->id(),
            'fecha' => now()->toIso8601String(),
        ]);
    }

    /**
     * Log de inconsistencia detectada
     */
    public static function logInconsistenciaDetectada(string $tipoDocumento, int $documentoId, string $descripcion): void
    {
        Log::channel('inventory')->critical('Inconsistencia de inventario detectada', [
            'documento_id' => $documentoId,
            'tipo_documento' => $tipoDocumento,
            'descripcion' => $descripcion,
            'usuario_id' => auth()->id(),
            'fecha' => now()->toIso8601String(),
        ]);
    }

    /**
     * Log de anulación
     */
    public static function logAnulacion(string $tipoDocumento, int $documentoId, ?string $razon = null): void
    {
        Log::channel('inventory')->info("Anulación de {$tipoDocumento}", [
            'documento_id' => $documentoId,
            'tipo_documento' => $tipoDocumento,
            'razon' => $razon,
            'usuario_id' => auth()->id(),
            'fecha' => now()->toIso8601String(),
        ]);
    }
}
