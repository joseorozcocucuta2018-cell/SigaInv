<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TransformacionLineaTipo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormulaTransformacionDetalle extends Model
{
    protected $table = 'formula_transformacion_detalles';

    protected $fillable = [
        'formula_transformacion_id',
        'tipo_linea',
        'producto_id',
        'cantidad',
        'lote',
        'fecha_vencimiento',
        'costo_unitario',
    ];

    protected $casts = [
        'tipo_linea' => TransformacionLineaTipo::class,
        'cantidad' => 'decimal:3',
        'fecha_vencimiento' => 'date',
        'costo_unitario' => 'decimal:4',
    ];

    public function formula(): BelongsTo
    {
        return $this->belongsTo(FormulaTransformacion::class, 'formula_transformacion_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
