<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MovimientoCajaTipo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MovimientoCaja extends Model
{
    protected $table = 'movimientos_cajas';

    protected $fillable = [
        'caja_id',
        'usuario_id',
        'forma_pago_id',
        'fecha_movimiento',
        'tipo',
        'monto',
        'saldo_actual',
        'categoria',
        'referencia',
        'concepto',
        'traslado_destino_tipo',
        'traslado_destino_id',
        'documento_tipo',
        'documento_id',
    ];

    protected $casts = [
        'tipo' => MovimientoCajaTipo::class,
        'monto' => 'decimal:2',
        'saldo_actual' => 'decimal:2',
        'fecha_movimiento' => 'datetime',
    ];

    public function caja(): BelongsTo
    {
        return $this->belongsTo(Caja::class, 'caja_id');
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

    /**
     * Calcula el saldo actual basado en movimientos anteriores
     */
    public function calcularSaldo(): float
    {
        $caja = Caja::find($this->caja_id);
        if (! $caja) {
            return 0;
        }

        $saldoAnterior = $caja->saldo_inicial ?? 0;

        $ingresos = static::where('caja_id', $this->caja_id)
            ->where('tipo', MovimientoCajaTipo::INGRESO->value)
            ->where('id', '!=', $this->id ?? 0)
            ->sum('monto');

        $egresos = static::where('caja_id', $this->caja_id)
            ->whereIn('tipo', [MovimientoCajaTipo::EGRESO->value, MovimientoCajaTipo::TRASLADO->value])
            ->where('id', '!=', $this->id ?? 0)
            ->sum('monto');

        return $saldoAnterior + $ingresos - $egresos;
    }
}
