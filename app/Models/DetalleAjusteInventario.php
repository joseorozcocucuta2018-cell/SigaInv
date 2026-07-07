<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleAjusteInventario extends Model
{
    protected $table = 'detalle_ajustes_inventario';

    protected $fillable = [
        'ajuste_inventario_id',
        'producto_id',
        'stock_sistema',
        'stock_fisico',
        'diferencia',
        'costo_unitario',
    ];

    protected $casts = [
        'stock_sistema' => 'decimal:3',
        'stock_fisico' => 'decimal:3',
        'diferencia' => 'decimal:3',
        'costo_unitario' => 'decimal:4',
    ];

    public function ajusteInventario(): BelongsTo
    {
        return $this->belongsTo(AjusteInventario::class, 'ajuste_inventario_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }
}
