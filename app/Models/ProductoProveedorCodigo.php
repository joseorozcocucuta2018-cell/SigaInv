<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductoProveedorCodigo extends Model
{
    use HasFactory;

    protected $table = 'producto_proveedor_codigos';

    protected $fillable = [
        'proveedor_id',
        'producto_id',
        'codigo_proveedor',
        'descripcion_proveedor',
    ];

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
