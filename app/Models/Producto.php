<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ProductoTipo;
use App\Models\Traits\SanitizesAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    use HasFactory;
    use SanitizesAttributes;

    protected $table = 'productos';

    protected $fillable = [
        'codigo',
        'codigo_barras',
        'nombre',
        'nombre_comun',
        'tipo_producto',
        'descripcion',
        'exige_lote',
        'exige_serial',
        'precio_compra',
        'costo_promedio',
        'precio_venta',
        'stock_minimo',
        'stock_maximo',
        'imagen',
        'activo',
        'con_formula',
        'tiene_movimientos',
        'categoria_id',
        'marca_id',
        'unidad_medida_id',
        'impuesto_id',
        'usuario_id',
    ];

    protected $casts = [
        'tipo_producto' => ProductoTipo::class,
        'activo' => 'boolean',
        'con_formula' => 'boolean',
        'tiene_movimientos' => 'boolean',
        'exige_lote' => 'boolean',
        'exige_serial' => 'boolean',
        'precio_compra' => 'decimal:2',
        'costo_promedio' => 'decimal:4',
        'precio_venta' => 'decimal:2',
        'stock_minimo' => 'decimal:3',
        'stock_maximo' => 'decimal:3',
    ];

    public function usaSeriales(): bool
    {
        return $this->exige_serial && Empresa::usaSeriales();
    }

    public function stockBodegas(): HasMany
    {
        return $this->hasMany(StockBodega::class, 'producto_id');
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function marca(): BelongsTo
    {
        return $this->belongsTo(Marca::class, 'marca_id');
    }

    public function unidadMedida(): BelongsTo
    {
        return $this->belongsTo(UnidadMedida::class, 'unidad_medida_id');
    }

    public function impuesto(): BelongsTo
    {
        return $this->belongsTo(Impuesto::class, 'impuesto_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function movimientosInventario(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class, 'producto_id');
    }

    public function historicoPrecios(): HasMany
    {
        return $this->hasMany(HistoricoPrecio::class, 'producto_id')->orderBy('fecha_cambio', 'desc');
    }

    public function codigosProveedores(): HasMany
    {
        return $this->hasMany(ProductoProveedorCodigo::class, 'producto_id');
    }

    public function formula(): HasMany
    {
        return $this->hasMany(FormulaTransformacion::class, 'producto_final_id');
    }

    /**
     * Determina si el producto tiene movimientos de inventario registrados.
     */
    public function tieneMovimientos(): bool
    {
        return $this->movimientosInventario()->exists();
    }

    /**
     * Determina si el producto puede ser eliminado o editado profundamente.
     */
    public function esInmutable(): bool
    {
        return (bool) $this->tiene_movimientos;
    }
}
