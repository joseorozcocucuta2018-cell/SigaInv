<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\AuditoriaDocumento;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait RegistraAuditoria
{
    /**
     * Registra una acción de auditoría
     */
    protected static function registrarAuditoria(
        string $documentoTipo,
        int $documentoId,
        string $accion,
        ?string $campo = null,
        $valorAnterior = null,
        $valorNuevo = null,
        ?string $estadoDocumento = null,
        ?string $observacion = null,
    ): void {
        try {
            AuditoriaDocumento::create([
                'documento_tipo' => $documentoTipo,
                'documento_id' => $documentoId,
                'usuario_id' => Auth::id(),
                'campo_modificado' => $campo,
                'valor_anterior' => is_string($valorAnterior) ? $valorAnterior : json_encode($valorAnterior),
                'valor_nuevo' => is_string($valorNuevo) ? $valorNuevo : json_encode($valorNuevo),
                'estado_documento' => $estadoDocumento,
                'accion' => $accion,
                'observacion' => $observacion,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]);
        } catch (\Exception $e) {
            // Silenciar errores de auditoría para no interrumpir la operación principal
            logger()->error('Error registrando auditoría', [
                'exception' => $e->getMessage(),
                'documento_tipo' => $documentoTipo,
                'documento_id' => $documentoId,
            ]);
        }
    }

    /**
     * Registra todos los cambios en un documento
     */
    protected static function registrarCambios(
        string $documentoTipo,
        int $documentoId,
        array $cambios,
        string $accion = 'update',
        ?string $estadoDocumento = null,
    ): void {
        foreach ($cambios as $campo => $valores) {
            static::registrarAuditoria(
                documentoTipo: $documentoTipo,
                documentoId: $documentoId,
                accion: $accion,
                campo: $campo,
                valorAnterior: $valores['old'] ?? null,
                valorNuevo: $valores['new'] ?? null,
                estadoDocumento: $estadoDocumento,
            );
        }
    }
}
