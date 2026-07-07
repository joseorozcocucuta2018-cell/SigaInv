<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DevolucionEstado;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DevolucionCompra extends Model
{
    use SoftDeletes;

    protected $table = 'devoluciones_compras';

    /**
     * Decisión de diseño (Tarea 6.5 — auditoría 2026-06-04):
     * El campo 'motivo' es varchar(50) libre (no enum) a diferencia
     * de Devolucion::motivo que usa el enum cerrado DevolucionMotivo.
     * Razón: las devoluciones a proveedor admiten texto libre acordado
     * bilateralmente (ej. "Acuerdo comercial 2026-Q2", "Reposición lote X"),
     * mientras que las devoluciones de cliente requieren un catálogo
     * controlado para reportes. NO migrar a enum.
     */
    protected $fillable = [
        'numero',
        'compra_id',
        'proveedor_id',
        'bodega_id',
        'estado',
        'motivo',
        'fecha',
        'observaciones',
        'subtotal',
        'descuento',
        'impuestos',
        'total',
        'confirmada_en',
        'anulada_en',
        'usuario_id',
    ];

    protected $casts = [
        'estado' => DevolucionEstado::class,
        'fecha' => 'date',
        'confirmada_en' => 'datetime',
        'anulada_en' => 'datetime',
        'subtotal' => 'decimal:2',
        'descuento' => 'decimal:2',
        'impuestos' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function compra(): BelongsTo
    {
        return $this->belongsTo(Compra::class, 'compra_id');
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function bodega(): BelongsTo
    {
        return $this->belongsTo(Bodega::class, 'bodega_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleDevolucionCompra::class, 'devolucion_compra_id');
    }
}
