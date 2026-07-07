<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TipoCalculoPrecio;
use App\Enums\TipoPromo;
use App\Enums\TransformacionEstado;
use App\Enums\TransformacionTipo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transformacion extends Model
{
    use SoftDeletes;

    protected $table = 'transformaciones';

    protected $fillable = [
        'bodega_id',
        'tipo',
        'tipo_promo',
        'fecha_vencimiento',
        'producto_final_id',
        'estado',
        'confirmada_en',
        'revertida_en',
        'fecha',
        'observaciones',
        'usuario_id',
        'formula_transformacion_id',
        'cantidad_a_producir',
        'tipo_calculo_precio',
        'costo_total',
        'precio_sugerido',
        'margen_deseado',
    ];

    protected $casts = [
        'tipo' => TransformacionTipo::class,
        'tipo_promo' => TipoPromo::class,
        'tipo_calculo_precio' => TipoCalculoPrecio::class,
        'estado' => TransformacionEstado::class,
        'fecha' => 'datetime',
        'fecha_vencimiento' => 'datetime',
        'confirmada_en' => 'datetime',
        'revertida_en' => 'datetime',
        'cantidad_a_producir' => 'decimal:3',
        'costo_total' => 'decimal:2',
        'precio_sugerido' => 'decimal:2',
        'margen_deseado' => 'decimal:2',
    ];

    /**
     * Obtiene los insumos (tipo_linea = insumo)
     */
    public function insumos(): HasMany
    {
        return $this->hasMany(TransformacionDetalle::class, 'transformacion_id')
            ->where('tipo_linea', 'insumo');
    }

    /**
     * Obtiene los productos resultado (tipo_linea = producto)
     */
    public function productos(): HasMany
    {
        return $this->hasMany(TransformacionDetalle::class, 'transformacion_id')
            ->where('tipo_linea', 'producto');
    }

    public function productoFinal(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_final_id');
    }

    public function bodega(): BelongsTo
    {
        return $this->belongsTo(Bodega::class, 'bodega_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function formula(): BelongsTo
    {
        return $this->belongsTo(FormulaTransformacion::class, 'formula_transformacion_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(TransformacionDetalle::class, 'transformacion_id');
    }

    /**
     * Verifica si la transformación es reversible
     * Solo Combo y Promo pueden revertirse
     */
    public function esReversible(): bool
    {
        return $this->tipo->reversible();
    }

    /**
     * Verifica si puede confirmarse
     */
    public function puedeConfirmarse(): bool
    {
        return $this->estado->canConfirm()
            && $this->producto_final_id !== null
            && $this->insumos()->count() > 0;
    }

    /**
     * Verifica si puede revertirse
     */
    public function puedeRevertirse(): bool
    {
        if (! $this->estado->canRevert()) {
            return false;
        }

        if (! $this->esReversible()) {
            return false;
        }

        // Si es promoción, verificar que no esté vencida
        if ($this->tipo->isPromo() && $this->fecha_vencimiento) {
            return $this->fecha_vencimiento->isFuture();
        }

        return true;
    }

    /**
     * Valida que la transformación sea confirmable
     */
    public function validarConfirmable(): void
    {
        if (! $this->estado->canConfirm()) {
            throw new \InvalidArgumentException(
                "No se puede confirmar una transformación en estado {$this->estado->label()}"
            );
        }

        if ($this->detalles()->count() === 0) {
            throw new \InvalidArgumentException('No se puede confirmar sin detalles');
        }

        if ($this->insumos()->count() === 0) {
            throw new \InvalidArgumentException('Debe agregar al menos un insumo');
        }

        if (! $this->producto_final_id) {
            throw new \InvalidArgumentException('Debe seleccionar el producto final de la transformación');
        }
    }

    /**
     * Scope para filtros comunes
     */
    public function scopeConfirmadas($query)
    {
        return $query->where('estado', TransformacionEstado::CONFIRMADA);
    }

    public function scopeBorrador($query)
    {
        return $query->where('estado', TransformacionEstado::BORRADOR);
    }

    public function scopeReversibles($query)
    {
        return $query->whereIn('tipo', [
            TransformacionTipo::COMBO->value,
            TransformacionTipo::PROMO->value,
        ])->where('estado', TransformacionEstado::CONFIRMADA);
    }

    public function scopePromosVencidas($query)
    {
        return $query->where('tipo', TransformacionTipo::PROMO->value)
            ->where('estado', TransformacionEstado::CONFIRMADA)
            ->whereDate('fecha_vencimiento', '<', now()->toDateString());
    }
}
