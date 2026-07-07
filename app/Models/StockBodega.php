<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockBodega extends Model
{
    use HasFactory;

    protected $table = 'stock_bodegas';

    protected $fillable = [
        'producto_id',
        'bodega_id',
        'cantidad',
        'ubicacion',
    ];

    protected $casts = [
        'cantidad' => 'decimal:3',
    ];

    protected static function booted(): void
    {
        static::creating(function (StockBodega $stock): void {
            if (blank($stock->ubicacion)) {
                $stock->ubicacion = 'DEFAULT';
            }
        });
    }

    public function getUbicacionAttribute(?string $value): string
    {
        return $value ?: 'DEFAULT';
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function bodega(): BelongsTo
    {
        return $this->belongsTo(Bodega::class, 'bodega_id');
    }

    public function lotes(): HasMany
    {
        return $this->hasMany(StockBodegaLote::class, 'stock_bodega_id');
    }

    public function seriales(): HasMany
    {
        return $this->hasMany(StockBodegaSerial::class, 'stock_bodega_id');
    }

    public function getLoteResumenAttribute(): string
    {
        if ($this->relationLoaded('lotes') ? $this->lotes->isEmpty() : ! $this->lotes()->exists()) {
            return 'Sin lotes';
        }

        $lotes = $this->relationLoaded('lotes') ? $this->lotes : $this->lotes()->get();

        return $lotes
            ->map(function (StockBodegaLote $lote): string {
                $vencimiento = $lote->fecha_vencimiento
                    ? $lote->fecha_vencimiento->format('d/m/Y')
                    : 'Sin vencimiento';

                return sprintf(
                    '%s — %s (%.3f)',
                    $lote->lote ?: 'Sin lote',
                    $vencimiento,
                    $lote->cantidad
                );
            })
            ->implode("\n");
    }

    public function getSerialResumenAttribute(): string
    {
        if ($this->relationLoaded('seriales') ? $this->seriales->isEmpty() : ! $this->seriales()->exists()) {
            return 'Sin seriales';
        }

        $seriales = $this->relationLoaded('seriales') ? $this->seriales : $this->seriales()->get();

        return $seriales
            ->map(function (StockBodegaSerial $serial): string {
                $estado = $serial->status ?? 'desconocido';

                return sprintf(
                    '%s — %s',
                    $serial->serial,
                    $estado
                );
            })
            ->implode("\n");
    }
}
