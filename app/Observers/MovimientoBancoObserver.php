<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Banco;
use App\Models\MovimientoBanco;
use Illuminate\Support\Facades\DB;

class MovimientoBancoObserver
{
    public function creating(MovimientoBanco $movimientoBanco): void
    {
        if (empty($movimientoBanco->saldo_actual)) {
            $saldoPrevio = $movimientoBanco->calcularSaldo();
            $movimientoBanco->saldo_actual = in_array($movimientoBanco->tipo, ['retiro', 'transferencia'], true)
                ? $saldoPrevio - (float) $movimientoBanco->monto
                : $saldoPrevio + (float) $movimientoBanco->monto;
        }
    }

    public function updated(MovimientoBanco $movimientoBanco): void
    {
        if ($movimientoBanco->isDirty(['monto', 'tipo'])) {
            static::recalcularSaldosPosteriores($movimientoBanco);
        }
    }

    public function deleted(MovimientoBanco $movimientoBanco): void
    {
        static::recalcularSaldosPosteriores($movimientoBanco, true);
    }

    private static function recalcularSaldosPosteriores(MovimientoBanco $movimientoBanco, bool $esEliminacion = false): void
    {
        $bancoId = $movimientoBanco->banco_id;

        DB::transaction(function () use ($bancoId, $movimientoBanco, $esEliminacion): void {
            $banco = Banco::lockForUpdate()->find($bancoId);

            $movimientosPosteriores = MovimientoBanco::where('banco_id', $bancoId)
                ->where(function ($query) use ($movimientoBanco) {
                    $query->where('fecha_movimiento', '>', $movimientoBanco->fecha_movimiento)
                        ->orWhere(function ($q) use ($movimientoBanco) {
                            $q->where('fecha_movimiento', '=', $movimientoBanco->fecha_movimiento)
                                ->where('id', '>', $movimientoBanco->id);
                        });
                })
                ->orderBy('fecha_movimiento')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $saldoBase = $banco ? ($banco->saldo_inicial ?? 0) : 0;

            $movimientosAnteriores = MovimientoBanco::where('banco_id', $bancoId)
                ->where(function ($query) use ($movimientoBanco, $esEliminacion) {
                    if ($esEliminacion) {
                        $query->where('fecha_movimiento', '<', $movimientoBanco->fecha_movimiento)
                            ->orWhere(function ($q) use ($movimientoBanco) {
                                $q->where('fecha_movimiento', '=', $movimientoBanco->fecha_movimiento)
                                    ->where('id', '<', $movimientoBanco->id);
                            });
                    } else {
                        $query->where('fecha_movimiento', '<', $movimientoBanco->fecha_movimiento)
                            ->orWhere(function ($q) use ($movimientoBanco) {
                                $q->where('fecha_movimiento', '=', $movimientoBanco->fecha_movimiento)
                                    ->where('id', '<=', $movimientoBanco->id);
                            });
                    }
                })
                ->orderBy('fecha_movimiento')
                ->orderBy('id')
                ->get();

            foreach ($movimientosAnteriores as $mov) {
                if ($mov->tipo === 'deposito') {
                    $saldoBase += $mov->monto;
                } else {
                    $saldoBase -= $mov->monto;
                }
            }

            $saldoActual = $saldoBase;

            foreach ($movimientosPosteriores as $mov) {
                if ($mov->tipo === 'deposito') {
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
