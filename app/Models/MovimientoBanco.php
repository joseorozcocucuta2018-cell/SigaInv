<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MovimientoBancoTipo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MovimientoBanco extends Model
{
    protected $table = 'movimientos_bancos';

    protected $fillable = [
        'banco_id',
        'usuario_id',
        'forma_pago_id',
        'fecha_movimiento',
        'tipo',
        'monto',
        'saldo_actual',
        'referencia',
        'concepto',
        'traslado_destino_tipo',
        'traslado_destino_id',
        'documento_tipo',
        'documento_id',
    ];

    protected $casts = [
        'tipo' => MovimientoBancoTipo::class,
        'fecha_movimiento' => 'datetime',
        'monto' => 'decimal:2',
        'saldo_actual' => 'decimal:2',
    ];

    public function banco(): BelongsTo
    {
        return $this->belongsTo(Banco::class, 'banco_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function formaPago(): BelongsTo
    {
        return $this->belongsTo(FormaPago::class, 'forma_pago_id');
    }

    public function trasladoDestino(): MorphTo
    {
        return $this->morphTo('trasladoDestino', 'traslado_destino_tipo', 'traslado_destino_id');
    }

    public function calcularSaldo(): float
    {
        $banco = Banco::find($this->banco_id);

        if (! $banco) {
            return 0;
        }

        $saldoAnterior = $banco->saldo_inicial ?? 0;

        $ingresos = static::where('banco_id', $this->banco_id)
            ->where('tipo', MovimientoBancoTipo::DEPOSITO->value)
            ->where('id', '!=', $this->id ?? 0)
            ->sum('monto');

        $egresos = static::where('banco_id', $this->banco_id)
            ->whereIn('tipo', [MovimientoBancoTipo::RETIRO->value, MovimientoBancoTipo::TRANSFERENCIA->value])
            ->where('id', '!=', $this->id ?? 0)
            ->sum('monto');

        return $saldoAnterior + $ingresos - $egresos;
    }
}
