<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Compra;
use App\Traits\RegistraAuditoria;

class CompraObserver
{
    use RegistraAuditoria;

    /**
     * Registra cambios de campos en compras
     */
    public function updated(Compra $compra): void
    {
        $dirty = $compra->getDirty();
        $original = $compra->getOriginal();

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
                documentoTipo: 'compra',
                documentoId: $compra->id,
                accion: 'update',
                campo: $campo,
                valorAnterior: $valorAnterior,
                valorNuevo: $valorNuevo,
                estadoDocumento: $compra->estado->value,
                observacion: "Actualización de campo: {$campo}",
            );
        }
    }
}
