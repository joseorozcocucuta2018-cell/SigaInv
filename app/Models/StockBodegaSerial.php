<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockBodegaSerial extends Model
{
    protected $table = 'stock_bodega_serials';

    protected $fillable = [
        'stock_bodega_id',
        'serial',
        'status',
        'lote',
        'fecha_vencimiento',
    ];

    protected $casts = [
        'fecha_vencimiento' => 'date',
    ];

    public function stock(): BelongsTo
    {
        return $this->belongsTo(StockBodega::class, 'stock_bodega_id');
    }
}
