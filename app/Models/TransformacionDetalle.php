<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransformacionDetalle extends Model
{
    use SoftDeletes;

    protected $table = 'transformacion_detalles';

    protected $fillable = [
        'transformacion_id',
        'tipo_linea',
        'producto_id',
        'cantidad',
        'lote',
        'serial',
        'fecha_vencimiento',
        'costo_unitario',
    ];

    protected $casts = [
        'cantidad' => 'decimal:3',
        'costo_unitario' => 'decimal:2',
        'fecha_vencimiento' => 'date',
    ];

    public function transformacion(): BelongsTo
    {
        return $this->belongsTo(Transformacion::class, 'transformacion_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
