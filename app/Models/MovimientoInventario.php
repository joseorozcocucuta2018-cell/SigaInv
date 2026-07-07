<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MovimientoInventarioTipo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoInventario extends Model
{
    protected $table = 'movimientos_inventario';

    protected $fillable = [
        'producto_id',
        'bodega_id',
        'tipo_movimiento',
        'cantidad',
        'costo_unitario',
        'lote',
        'fecha_vencimiento',
        'stock_resultante',
        'documento_tipo',
        'documento_id',
        'detalle_compra_id',
        'detalle_venta_id',
        'detalle_remision_id',
        'observacion',
        'usuario_id',
        'fecha_movimiento',
    ];

    protected $casts = [
        'tipo_movimiento' => MovimientoInventarioTipo::class,
        'fecha_movimiento' => 'datetime',
        'cantidad' => 'decimal:3',
        'costo_unitario' => 'decimal:2',
        'lote' => 'string',
        'fecha_vencimiento' => 'date',
        'stock_resultante' => 'decimal:3',
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function bodega(): BelongsTo
    {
        return $this->belongsTo(Bodega::class, 'bodega_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function detalleCompra(): BelongsTo
    {
        return $this->belongsTo(DetalleCompra::class, 'detalle_compra_id');
    }

    public function detalleVenta(): BelongsTo
    {
        return $this->belongsTo(DetalleVenta::class, 'detalle_venta_id');
    }

    public function detalleRemision(): BelongsTo
    {
        return $this->belongsTo(DetalleRemision::class, 'detalle_remision_id');
    }
}
