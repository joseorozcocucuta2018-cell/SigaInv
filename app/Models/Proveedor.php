<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DocumentoTipoEnum;
use App\Enums\ProveedorEstado;
use App\Models\Traits\SanitizesAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Proveedor extends Model
{
    use HasFactory;
    use SanitizesAttributes;

    protected $table = 'proveedores';

    protected $fillable = [
        'nombre',
        'documento',
        'tipo_documento',
        'telefono',
        'email',
        'direccion1',
        'direccion2',
        'departamento_id',
        'ciudad_id',
        'saldo',
        'pais',
        'estado',
        'limite_credito',
        'dias_credito',
        'dias_pago',
        'contacto_principal',
        'sitio_web',
        'usuario_id',
    ];

    protected $casts = [
        'estado' => ProveedorEstado::class,
        'tipo_documento' => DocumentoTipoEnum::class,
        'saldo' => 'decimal:2',
        'limite_credito' => 'decimal:2',
    ];

    public function ciudad(): BelongsTo
    {
        return $this->belongsTo(Ciudad::class, 'ciudad_id');
    }

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class, 'departamento_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function codigosProductos(): HasMany
    {
        return $this->hasMany(ProductoProveedorCodigo::class, 'proveedor_id');
    }

    public function compras(): HasMany
    {
        return $this->hasMany(Compra::class, 'proveedor_id');
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(PagoProveedor::class, 'proveedor_id');
    }

    public function detallesCompra(): HasManyThrough
    {
        return $this->hasManyThrough(
            DetalleCompra::class,
            Compra::class,
            'proveedor_id',
            'compra_id',
            'id',
            'id',
        );
    }
}
