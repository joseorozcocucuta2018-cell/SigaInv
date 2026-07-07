<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AjusteEstado;
use App\Enums\MotivoAjuste;
use App\Traits\HasAutoNumbering;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AjusteInventario extends Model
{
    use HasAutoNumbering, HasFactory;

    protected string $autoNumberPrefix = 'AJU-';

    protected int $autoNumberOffset = 4;

    protected $table = 'ajustes_inventario';

    protected $fillable = [
        'numero',
        'bodega_id',
        'usuario_id',
        'fecha',
        'motivo',
        'estado',
        'es_saldo_inicial',
        'observacion',
        'confirmado_en',
    ];

    protected $casts = [
        'estado' => AjusteEstado::class,
        'motivo' => MotivoAjuste::class,
        'es_saldo_inicial' => 'boolean',
        'fecha' => 'date',
        'confirmado_en' => 'datetime',
    ];

    public function bodega(): BelongsTo
    {
        return $this->belongsTo(Bodega::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleAjusteInventario::class, 'ajuste_inventario_id');
    }
}
