<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ConteoFisicoEstado;
use App\Traits\HasAutoNumbering;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConteoFisico extends Model
{
    use HasAutoNumbering, HasFactory;

    protected string $autoNumberPrefix = 'CNT-';

    protected int $autoNumberOffset = 4;

    protected $table = 'conteos_fisicos';

    protected $fillable = [
        'numero',
        'bodega_id',
        'usuario_id',
        'fecha_inicio',
        'fecha_cierre',
        'estado',
        'es_saldo_inicial',
        'observacion',
    ];

    protected $casts = [
        'estado' => ConteoFisicoEstado::class,
        'es_saldo_inicial' => 'boolean',
        'fecha_inicio' => 'date',
        'fecha_cierre' => 'date',
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
        return $this->hasMany(DetalleConteoFisico::class, 'conteo_fisico_id');
    }
}
