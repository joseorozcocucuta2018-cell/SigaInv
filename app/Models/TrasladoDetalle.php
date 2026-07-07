<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrasladoDetalle extends Model
{
    use SoftDeletes;

    protected $table = 'traslado_detalles';

    protected $fillable = [
        'traslado_id',
        'producto_id',
        'cantidad',
        'lote',
        'fecha_vencimiento',
    ];

    protected $casts = [
        'cantidad' => 'decimal:3',
        'fecha_vencimiento' => 'date',
    ];

    public function traslado(): BelongsTo
    {
        return $this->belongsTo(Traslado::class, 'traslado_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
