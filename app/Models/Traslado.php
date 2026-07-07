<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TrasladoEstado;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Traslado extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'traslados';

    protected $fillable = [
        'bodega_origen_id',
        'bodega_destino_id',
        'estado',
        'confirmada_en',
        'revertida_en',
        'fecha',
        'observaciones',
        'usuario_id',
    ];

    protected $casts = [
        'estado' => TrasladoEstado::class,
        'confirmada_en' => 'datetime',
        'revertida_en' => 'datetime',
        'fecha' => 'datetime',
    ];

    public function bodegaOrigen(): BelongsTo
    {
        return $this->belongsTo(Bodega::class, 'bodega_origen_id');
    }

    public function bodegaDestino(): BelongsTo
    {
        return $this->belongsTo(Bodega::class, 'bodega_destino_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(TrasladoDetalle::class, 'traslado_id');
    }

    public function puedeConfirmar(): bool
    {
        return $this->estado?->value === TrasladoEstado::BORRADOR->value && $this->detalles()->exists();
    }

    public function puedeRevertir(): bool
    {
        return $this->estado?->value === TrasladoEstado::CONFIRMADA->value;
    }
}
