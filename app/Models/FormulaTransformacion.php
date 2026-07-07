<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TransformacionTipo;
use App\Models\Traits\SanitizesAttributes;
use Database\Factories\FormulaTransformacionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormulaTransformacion extends Model
{
    /** @use HasFactory<FormulaTransformacionFactory> */
    use HasFactory;

    use SanitizesAttributes;

    protected $table = 'formula_transformaciones';

    protected $fillable = [
        'descripcion',
        'tipo',
        'producto_final_id',
        'producto_final_nombre',
        'cantidad_producto_final',
        'activo',
        'usuario_id',
        'bloqueada',
    ];

    protected $casts = [
        'tipo' => TransformacionTipo::class,
        'cantidad_producto_final' => 'decimal:3',
        'activo' => 'boolean',
        'bloqueada' => 'boolean',
        'tiene_transformaciones' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saved(function ($formula) {
            if ($formula->producto_final_id) {
                // Si el producto cambió, marcar el anterior como false
                if ($formula->wasChanged('producto_final_id')) {
                    $oldId = $formula->getOriginal('producto_final_id');
                    if ($oldId) {
                        Producto::where('id', $oldId)->update(['con_formula' => false]);
                    }
                }
                Producto::where('id', $formula->producto_final_id)->update(['con_formula' => true]);
            }
        });

        static::deleted(function ($formula) {
            if ($formula->producto_final_id) {
                Producto::where('id', $formula->producto_final_id)->update(['con_formula' => false]);
            }
        });
    }

    public function productoFinal(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_final_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(FormulaTransformacionDetalle::class, 'formula_transformacion_id');
    }

    public function transformaciones(): HasMany
    {
        return $this->hasMany(Transformacion::class, 'formula_transformacion_id');
    }

    public function tieneTransformaciones(): bool
    {
        return $this->transformaciones()->exists();
    }
}
