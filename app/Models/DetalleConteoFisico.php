<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleConteoFisico extends Model
{
    protected $table = 'detalle_conteos_fisicos';

    protected $fillable = [
        'conteo_fisico_id',
        'producto_id',
        'stock_sistema',
        'cantidad_contada',
        'diferencia',
        'ajuste_inventario_id',
    ];

    protected $casts = [
        'stock_sistema' => 'decimal:3',
        'cantidad_contada' => 'decimal:3',
        'diferencia' => 'decimal:3',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $detalle) {
            if ($detalle->isDirty('cantidad_contada') && $detalle->cantidad_contada !== null) {
                $detalle->diferencia = (float) $detalle->cantidad_contada - (float) $detalle->stock_sistema;
            }
        });
    }

    public function conteoFisico(): BelongsTo
    {
        return $this->belongsTo(ConteoFisico::class, 'conteo_fisico_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function ajusteInventario(): BelongsTo
    {
        return $this->belongsTo(AjusteInventario::class, 'ajuste_inventario_id');
    }
}
