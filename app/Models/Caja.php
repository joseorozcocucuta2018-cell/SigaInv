<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CajaEstado;
use App\Enums\CajaTipo;
use App\Models\Traits\SanitizesAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Caja extends Model
{
    use SanitizesAttributes;

    protected $table = 'cajas';

    protected $fillable = [
        'nombre',
        'tipo',
        'saldo_inicial',
        'activo',
        'estado',
        'usuario_id',
    ];

    protected $casts = [
        'saldo_inicial' => 'decimal:2',
        'activo' => 'boolean',
        'estado' => CajaEstado::class,
        'tipo' => CajaTipo::class,
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoCaja::class, 'caja_id');
    }

    public function turnos(): HasMany
    {
        return $this->hasMany(Turno::class, 'caja_id');
    }

    /**
     * Verifica si la caja tiene movimientos registrados.
     */
    public function tieneMovimientos(): bool
    {
        return $this->movimientos()->exists();
    }

    /**
     * Calcula el saldo actual de la caja
     */
    public function getSaldoActualAttribute(): float
    {
        $ultimoMovimiento = $this->movimientos()
            ->orderBy('fecha_movimiento', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        return $ultimoMovimiento
            ? (float) $ultimoMovimiento->saldo_actual
            : (float) $this->saldo_inicial;
    }
}
