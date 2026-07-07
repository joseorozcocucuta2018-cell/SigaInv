<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Caja;
use App\Models\MovimientoCaja;
use Illuminate\Support\Facades\DB;

class MovimientoCajaObserver
{
    public function creating(MovimientoCaja $movimientoCaja): void
    {
        if (empty($movimientoCaja->saldo_actual)) {
            $saldoPrevio = $movimientoCaja->calcularSaldo();
            $movimientoCaja->saldo_actual = in_array($movimientoCaja->tipo, ['egreso', 'traslado'], true)
                ? $saldoPrevio - (float) $movimientoCaja->monto
                : $saldoPrevio + (float) $movimientoCaja->monto;
        }
    }

    public function updated(MovimientoCaja $movimientoCaja): void
    {
        if ($movimientoCaja->isDirty(['monto', 'tipo'])) {
            static::recalcularSaldosPosteriores($movimientoCaja);
        }
    }

    public function deleted(MovimientoCaja $movimientoCaja): void
    {
        static::recalcularSaldosPosteriores($movimientoCaja, true);
    }

    private static function recalcularSaldosPosteriores(MovimientoCaja $movimientoCaja, bool $esEliminacion = false): void
    {
        $cajaId = $movimientoCaja->caja_id;

        DB::transaction(function () use ($cajaId, $movimientoCaja, $esEliminacion): void {
            $caja = Caja::lockForUpdate()->find($cajaId);

            $movimientosPosteriores = MovimientoCaja::where('caja_id', $cajaId)
                ->where(function ($query) use ($movimientoCaja) {
                    $query->where('fecha_movimiento', '>', $movimientoCaja->fecha_movimiento)
                        ->orWhere(function ($q) use ($movimientoCaja) {
                            $q->where('fecha_movimiento', '=', $movimientoCaja->fecha_movimiento)
                                ->where('id', '>', $movimientoCaja->id);
                        });
                })
                ->orderBy('fecha_movimiento')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $saldoBase = $caja ? ($caja->saldo_inicial ?? 0) : 0;

            $movimientosAnteriores = MovimientoCaja::where('caja_id', $cajaId)
                ->where(function ($query) use ($movimientoCaja, $esEliminacion) {
                    if ($esEliminacion) {
                        $query->where('fecha_movimiento', '<', $movimientoCaja->fecha_movimiento)
                            ->orWhere(function ($q) use ($movimientoCaja) {
                                $q->where('fecha_movimiento', '=', $movimientoCaja->fecha_movimiento)
                                    ->where('id', '<', $movimientoCaja->id);
                            });
                    } else {
                        $query->where('fecha_movimiento', '<', $movimientoCaja->fecha_movimiento)
                            ->orWhere(function ($q) use ($movimientoCaja) {
                                $q->where('fecha_movimiento', '=', $movimientoCaja->fecha_movimiento)
                                    ->where('id', '<=', $movimientoCaja->id);
                            });
                    }
                })
                ->orderBy('fecha_movimiento')
                ->orderBy('id')
                ->get();

            foreach ($movimientosAnteriores as $mov) {
                if ($mov->tipo === 'ingreso') {
                    $saldoBase += $mov->monto;
                } else {
                    $saldoBase -= $mov->monto;
                }
            }

            $saldoActual = $saldoBase;

            foreach ($movimientosPosteriores as $mov) {
                if ($mov->tipo === 'ingreso') {
                    $saldoActual += $mov->monto;
                } else {
                    $saldoActual -= $mov->monto;
                }

                $mov->saldo_actual = $saldoActual;
                $mov->saveQuietly();
            }
        });
    }
}
