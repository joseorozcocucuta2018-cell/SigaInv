<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DevolucionEstado;
use App\Enums\DevolucionMotivo;
use App\Enums\DevolucionTipoDocumento;
use App\Services\DevolucionService;
use App\Traits\HasAutoNumbering;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Devolucion extends Model
{
    use HasAutoNumbering, HasFactory, SoftDeletes;

    protected string $autoNumberPrefix = 'DVL-';

    protected bool $autoNumberUseLastSegment = true;

    protected $table = 'devoluciones';

    protected $fillable = [
        'numero',
        'tipo_documento',
        'documento_id',
        'cliente_id',
        'estado',
        'motivo',
        'observaciones',
        'subtotal',
        'descuento',
        'impuestos',
        'total',
        'usuario_id',
        'confirmada_en',
    ];

    protected $casts = [
        'tipo_documento' => DevolucionTipoDocumento::class,
        'estado' => DevolucionEstado::class,
        'motivo' => DevolucionMotivo::class,
        'confirmada_en' => 'datetime',
        'subtotal' => 'decimal:2',
        'descuento' => 'decimal:2',
        'impuestos' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * Flag para evitar doble procesamiento cuando el Service actualiza el estado.
     */
    public bool $skipProcessing = false;

    protected static function booted(): void
    {
        static::updating(function (Devolucion $devolucion): void {
            if ($devolucion->isDirty('estado') && $devolucion->estado?->value === DevolucionEstado::CONFIRMADA->value && ! $devolucion->skipProcessing) {
                $devolucion->confirmada_en = now();
                app(DevolucionService::class)->procesarConfirmacion($devolucion);
            }
        });
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleDevolucion::class, 'devolucion_id');
    }

    public function remision(): ?BelongsTo
    {
        if ($this->tipo_documento === 'remision') {
            return $this->belongsTo(Remision::class, 'documento_id');
        }

        return null;
    }

    public function venta(): ?BelongsTo
    {
        if ($this->tipo_documento === 'venta') {
            return $this->belongsTo(Venta::class, 'documento_id');
        }

        return null;
    }
}
