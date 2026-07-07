<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleDevolucionCompra extends Model
{
    protected $table = 'detalles_devoluciones_compras';

    protected $fillable = [
        'devolucion_compra_id',
        'producto_id',
        'cantidad',
        'precio_unitario',
        'subtotal',
        'costo_unitario',
    ];

    protected $casts = [
        'cantidad' => 'decimal:3',
        'precio_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'costo_unitario' => 'decimal:4',
    ];

    public function devolucionCompra(): BelongsTo
    {
        return $this->belongsTo(DevolucionCompra::class, 'devolucion_compra_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
