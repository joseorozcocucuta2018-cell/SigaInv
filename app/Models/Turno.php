<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TurnoEstado;
use App\Models\Traits\SanitizesAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Turno extends Model
{
    use SanitizesAttributes;

    protected $table = 'turnos';

    protected $fillable = [
        'caja_id',
        'bodega_id',
        'usuario_id',
        'fecha_apertura',
        'fecha_cierre',
        'saldo_inicial',
        'saldo_final_esperado',
        'saldo_final_real',
        'diferencia',
        'estado',
    ];

    protected $casts = [
        'fecha_apertura' => 'datetime',
        'fecha_cierre' => 'datetime',
        'saldo_inicial' => 'decimal:2',
        'saldo_final_esperado' => 'decimal:2',
        'saldo_final_real' => 'decimal:2',
        'diferencia' => 'decimal:2',
        'estado' => TurnoEstado::class,
    ];

    public function caja(): BelongsTo
    {
        return $this->belongsTo(Caja::class, 'caja_id');
    }

    public function bodega(): BelongsTo
    {
        return $this->belongsTo(Bodega::class, 'bodega_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
