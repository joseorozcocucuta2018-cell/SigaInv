<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CompraEstado;
use App\Traits\ProtegidoRestauracion;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Compra extends Model
{
    use HasFactory, ProtegidoRestauracion, SoftDeletes;

    protected $table = 'compras';

    protected $fillable = [
        'numero',
        'estado',
        'proveedor_id',
        'bodega_id',
        'usuario_id',
        'fecha',
        'subtotal',
        'descuento',
        'impuestos',
        'total',
        'saldo_pendiente',
        'fecha_vencimiento',
        'observaciones',
        'total_confirmado',
        'impuestos_confirmados',
        'snapshot_confirmacion',
        'confirmada_en',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Compra $compra): void {
            if (! $compra->estado->isEditable()) {
                throw new \InvalidArgumentException(
                    "No se puede eliminar una compra en estado {$compra->estado->label()}. Solo se pueden eliminar compras en estado Borrador."
                );
            }
        });
    }

    protected $attributes = [
        'saldo_pendiente' => 0,
    ];

    protected $casts = [
        'estado' => CompraEstado::class,
        'fecha' => 'datetime',
        'confirmada_en' => 'datetime',
        'fecha_vencimiento' => 'date',
        'subtotal' => 'decimal:2',
        'descuento' => 'decimal:2',
        'impuestos' => 'decimal:2',
        'total' => 'decimal:2',
        'saldo_pendiente' => 'decimal:2',
        'total_confirmado' => 'decimal:2',
        'impuestos_confirmados' => 'decimal:2',
        'snapshot_confirmacion' => 'array',
    ];

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
        return $this->hasMany(DetalleCompra::class, 'compra_id');
    }

    public function detallePagos(): HasMany
    {
        return $this->hasMany(DetallePagoProveedor::class, 'compra_id');
    }

    /**
     * Validar que la compra sea registrable
     * Se ejecuta antes de registrar desde el servicio
     */
    public function validarRegistrable(): void
    {
        if ($this->detalles()->count() === 0) {
            throw new \InvalidArgumentException('No se puede registrar una compra sin detalles');
        }

        foreach ($this->detalles as $detalle) {
            if ($detalle->cantidad <= 0) {
                throw new \InvalidArgumentException('La cantidad debe ser mayor a 0');
            }
            if ($detalle->precio_unitario < 0) {
                throw new \InvalidArgumentException('El precio unitario no puede ser negativo');
            }
        }
    }
}
