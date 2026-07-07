<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CotizacionEstado;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cotizacion extends Model
{
    protected $table = 'cotizaciones';

    protected $fillable = [
        'numero',
        'cliente_id',
        'bodega_id',
        'usuario_id',
        'fecha',
        'fecha_vigencia',
        'subtotal',
        'descuento',
        'impuestos',
        'total',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'estado' => CotizacionEstado::class,
        'fecha' => 'datetime',
        'fecha_vigencia' => 'date',
        'subtotal' => 'decimal:2',
        'descuento' => 'decimal:2',
        'impuestos' => 'decimal:2',
        'total' => 'decimal:2',
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

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleCotizacion::class, 'cotizacion_id');
    }
}
