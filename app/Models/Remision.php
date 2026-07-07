<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EstadoPagoEnum;
use App\Enums\RemisionEstado;
use App\Traits\ProtegidoRestauracion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Remision extends Model
{
    use ProtegidoRestauracion, SoftDeletes;

    protected $table = 'remisiones';

    protected $fillable = [
        'numero',
        'estado',
        'cliente_id',
        'bodega_id',
        'usuario_id',
        'cotizacion_id',
        'fecha',
        'subtotal',
        'descuento',
        'impuestos',
        'total',
        'saldo_pendiente',
        'estado_pago',
        'fecha_vencimiento',
        'observaciones',
        'total_confirmado',
        'impuestos_confirmados',
        'snapshot_confirmacion',
        'confirmada_en',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Remision $remision): void {
            if (! $remision->estado->isEditable()) {
                throw new \InvalidArgumentException(
                    "No se puede eliminar una remisión en estado {$remision->estado->label()}. Solo se pueden eliminar remisiones en estado Borrador."
                );
            }
        });
    }

    protected $casts = [
        'estado' => RemisionEstado::class,
        'estado_pago' => EstadoPagoEnum::class,
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

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function bodega(): BelongsTo
    {
        return $this->belongsTo(Bodega::class, 'bodega_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function cotizacion(): BelongsTo
    {
        return $this->belongsTo(Cotizacion::class, 'cotizacion_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleRemision::class, 'remision_id');
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class, 'remision_id');
    }

    /**
     * Validar que la remisión sea confirmable
     * Se ejecuta antes de confirmar desde el servicio
     */
    public function validarConfirmable(): void
    {
        if ($this->detalles()->count() === 0) {
            throw new \InvalidArgumentException('No se puede confirmar una remisión sin detalles');
        }

        // Validar que cada detalle tenga cantidad válida
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
