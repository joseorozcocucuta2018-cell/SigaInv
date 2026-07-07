<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\EstadoPagoEnum;
use App\Models\Remision;
use App\Services\NumeracionService;
use App\Traits\RegistraAuditoria;
use Illuminate\Support\Facades\Auth;

class RemisionObserver
{
    use RegistraAuditoria;

    /**
     * Registra cambios de campos en remisiones
     */
    public function updated(Remision $remision): void
    {
        $dirty = $remision->getDirty();
        $original = $remision->getOriginal();

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
                documentoTipo: 'remision',
                documentoId: $remision->id,
                accion: 'update',
                campo: $campo,
                valorAnterior: $valorAnterior,
                valorNuevo: $valorNuevo,
                estadoDocumento: $remision->estado->value,
                observacion: "Actualización de campo: {$campo}",
            );
        }
    }

    public function creating(Remision $remision): void
    {
        if (empty($remision->numero)) {
            try {
                $resultado = NumeracionService::obtenerSiguienteNumero('remision');
                $remision->numero = $resultado['numero'];
            } catch (\Exception $e) {
                // Para remisión (interno), si no hay configurado, podemos generar uno manual o fallar
                // Optamos por fallar para forzar al cliente a configurar su numeración
                throw $e;
            }
        }

        if (Auth::check() && ! $remision->usuario_id) {
            $remision->usuario_id = Auth::id();
        }
    }

    public function saving(Remision $remision): void
    {
        if ($remision->estado_pago === EstadoPagoEnum::PAGADO && $remision->saldo_pendiente > 0) {
            $remision->saldo_pendiente = 0;
        }

        if (! $remision->exists && (float) $remision->total > 0 && (float) $remision->saldo_pendiente == 0) {
            $remision->saldo_pendiente = $remision->total;
        }
    }
}
