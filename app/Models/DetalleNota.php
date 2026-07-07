<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleNota extends Model
{
    use HasFactory;

    protected $table = 'detalle_notas';

    protected $fillable = [
        'nota_id',
        'producto_id',
        'cantidad',
        'precio_unitario',
        'impuestos',
        'subtotal',
    ];

    protected $casts = [
        'cantidad' => 'decimal:3',
        'precio_unitario' => 'decimal:2',
        'impuestos' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function nota(): BelongsTo
    {
        return $this->belongsTo(Nota::class);
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }
}
