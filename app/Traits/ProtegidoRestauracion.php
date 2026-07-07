<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\MovimientoInventario;
use Illuminate\Database\Eloquent\Model;

/**
 * Protege restauración peligrosa de documentos con soft delete
 *
 * Un documento NO debe ser restaurado si tiene movimientos de inventario
 * asociados, ya que causaría inconsistencias en el stock.
 *
 * Req. 6: Bloquear restore peligroso con SoftDeletes
 */
trait ProtegidoRestauracion
{
    /**
     * Bloquea restore si tiene movimientos asociados
     */
    public static function bootProtegidoRestauracion()
    {
        static::restoring(function (Model $model) {
            // Mapear tipo de modelo a nombre de documento
            $tipoDocumento = match (class_basename($model)) {
                'Compra' => 'compra',
                'Venta' => 'venta',
                'Remision' => 'remision',
                default => null,
            };

            // Si hay movimientos asociados, bloquear
            if ($tipoDocumento && $model->movimientosInventario()->exists()) {
                throw new \InvalidArgumentException(
                    "No se puede restaurar un {$tipoDocumento} que tiene movimientos de inventario asociados. "
                    .'Esto causaría inconsistencias de stock.'
                );
            }
        });
    }

    /**
     * Relación con movimientos de inventario
     */
    public function movimientosInventario()
    {
        $tipoDocumento = match (class_basename($this)) {
            'Compra' => 'compra',
            'Venta' => 'venta',
            'Remision' => 'remision',
            default => null,
        };

        return $this->hasMany(
            MovimientoInventario::class,
            'documento_id'
        )->where('documento_tipo', $tipoDocumento);
    }
}
