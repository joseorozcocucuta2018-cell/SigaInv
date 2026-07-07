<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleRemision extends Model
{
    protected $table = 'detalle_remisiones';

    protected $fillable = [
        'remision_id',
        'producto_id',
        'cantidad',
        'precio_unitario',
        'descuento_unitario',
        'impuesto_id',
        'subtotal',
        'costo_unitario',
        'lote',
        'fecha_vencimiento',
        'serial',
    ];

    protected $casts = [
        'cantidad' => 'decimal:3',
        'precio_unitario' => 'decimal:2',
        'descuento_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'costo_unitario' => 'decimal:4',
        'fecha_vencimiento' => 'date',
    ];

    public function remision(): BelongsTo
    {
        return $this->belongsTo(Remision::class, 'remision_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function impuesto(): BelongsTo
    {
        return $this->belongsTo(Impuesto::class, 'impuesto_id');
    }
}
