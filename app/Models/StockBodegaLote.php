<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockBodegaLote extends Model
{
    protected $table = 'stock_bodega_lotes';

    protected $fillable = [
        'stock_bodega_id',
        'lote',
        'fecha_vencimiento',
        'cantidad',
    ];

    protected $casts = [
        'fecha_vencimiento' => 'date',
        'cantidad' => 'decimal:3',
    ];

    public function stock(): BelongsTo
    {
        return $this->belongsTo(StockBodega::class, 'stock_bodega_id');
    }
}
