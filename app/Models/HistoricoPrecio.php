<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoricoPrecio extends Model
{
    protected $table = 'historico_precios';

    protected $fillable = [
        'producto_id',
        'proveedor_id',
        'precio_compra',
        'usuario_id',
        'fecha_cambio',
    ];

    protected $casts = [
        'fecha_cambio' => 'datetime',
        'precio_compra' => 'decimal:2',
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }
}
