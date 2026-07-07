<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BancoEstado;
use App\Enums\BancoTipoCuenta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Banco extends Model
{
    protected $table = 'bancos';

    protected $fillable = [
        'nombre_banco',
        'numero_cuenta',
        'tipo_cuenta',
        'saldo_inicial',
        'moneda',
        'estado',
        'codigo_swift',
        'usuario_id',
    ];

    protected $casts = [
        'saldo_inicial' => 'decimal:2',
        'estado' => BancoEstado::class,
        'tipo_cuenta' => BancoTipoCuenta::class,
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoBanco::class, 'banco_id');
    }

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
