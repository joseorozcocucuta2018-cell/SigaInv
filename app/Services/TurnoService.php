<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\MovimientoCajaTipo;
use App\Enums\TurnoEstado;
use App\Models\Caja;
use App\Models\MovimientoCaja;
use App\Models\Turno;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Servicio para operaciones de Turno de Caja (POS)
 *
 * Maneja la apertura, cierre y consulta del turno activo de un usuario
 * en el Punto de Venta. Reutiliza MovimientoCaja como fuente de verdad
 * para el cálculo de ingresos (igual que el flujo legacy de sigaPos).
 */
class TurnoService
{
    /**
     * Abre un nuevo turno para el usuario autenticado.
     *
     * Valida que no exista un turno abierto para el mismo usuario.
     * El cierre recalcula siempre desde MovimientoCaja (no se persiste
     * el esperado en apertura).
     *
     * @param  array{caja_id:int, bodega_id?:int|null, saldo_inicial:float|string}  $data
     */
    public static function abrir(User $usuario, array $data): Turno
    {
        $cajaId = (int) ($data['caja_id'] ?? 0);
        $bodegaId = (int) ($data['bodega_id'] ?? 0) ?: null;
        $saldoInicial = (float) ($data['saldo_inicial'] ?? 0);

        if ($cajaId <= 0) {
            throw new InvalidArgumentException('caja_id es requerido.');
        }

        if ($saldoInicial < 0) {
            throw new InvalidArgumentException('El saldo inicial no puede ser negativo.');
        }

        $caja = Caja::find($cajaId);
        if (! $caja || ! $caja->activo) {
            throw new InvalidArgumentException('La caja no existe o está inactiva.');
        }

        if ($bodegaId !== null) {
            $bodegaExists = DB::table('bodegas')->where('id', $bodegaId)->exists();
            if (! $bodegaExists) {
                throw new InvalidArgumentException('La bodega no existe.');
            }
        }

        return DB::transaction(function () use ($usuario, $cajaId, $bodegaId, $saldoInicial) {
            $existente = Turno::where('usuario_id', $usuario->id)
                ->where('estado', TurnoEstado::ABIERTO)
                ->lockForUpdate()
                ->first();

            if ($existente) {
                throw new InvalidArgumentException(
                    'El usuario ya tiene un turno abierto. Ciérrelo antes de iniciar otro.'
                );
            }

            return Turno::create([
                'caja_id' => $cajaId,
                'bodega_id' => $bodegaId,
                'usuario_id' => $usuario->id,
                'fecha_apertura' => now(),
                'saldo_inicial' => $saldoInicial,
                'saldo_final_esperado' => $saldoInicial,
                'estado' => TurnoEstado::ABIERTO,
            ]);
        });
    }

    /**
     * Cierra el turno activo del usuario.
     *
     * Recalcula el saldo esperado desde MovimientoCaja (suma de ingresos
     * posteriores a la fecha de apertura). Registra la diferencia entre
     * el conteo físico y el esperado tal cual — sin maquillar.
     *
     * @return array{Turno, array{desglose_pagos: array<int,array{id:int,nombre:string,total:float}>}}
     */
    public static function cerrar(User $usuario, float $saldoFinalReal): array
    {
        if ($saldoFinalReal < 0) {
            throw new InvalidArgumentException('El saldo final real no puede ser negativo.');
        }

        return DB::transaction(function () use ($usuario, $saldoFinalReal) {
            $turno = Turno::where('usuario_id', $usuario->id)
                ->where('estado', TurnoEstado::ABIERTO)
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            if (! $turno) {
                throw new InvalidArgumentException('No tienes un turno abierto.');
            }

            $ingresos = (float) MovimientoCaja::where('usuario_id', $usuario->id)
                ->where('fecha_movimiento', '>=', $turno->fecha_apertura)
                ->where('tipo', MovimientoCajaTipo::INGRESO->value)
                ->sum('monto');

            $saldoEsperado = (float) $turno->saldo_inicial + $ingresos;
            $diferencia = $saldoFinalReal - $saldoEsperado;

            $turno->update([
                'fecha_cierre' => now(),
                'saldo_final_esperado' => $saldoEsperado,
                'saldo_final_real' => $saldoFinalReal,
                'diferencia' => $diferencia,
                'estado' => TurnoEstado::CERRADO,
            ]);

            $desglosePagos = MovimientoCaja::query()
                ->selectRaw('formas_pago.id as id, formas_pago.nombre as nombre, COALESCE(SUM(movimientos_cajas.monto), 0) as total')
                ->from('formas_pago')
                ->leftJoin('movimientos_cajas', function ($join) use ($usuario, $turno) {
                    $join->on('movimientos_cajas.forma_pago_id', '=', 'formas_pago.id')
                        ->where('movimientos_cajas.usuario_id', '=', $usuario->id)
                        ->where('movimientos_cajas.fecha_movimiento', '>=', $turno->fecha_apertura)
                        ->where('movimientos_cajas.tipo', '=', MovimientoCajaTipo::INGRESO->value);
                })
                ->where('formas_pago.activo', true)
                ->groupBy('formas_pago.id', 'formas_pago.nombre')
                ->orderBy('formas_pago.id')
                ->get()
                ->map(fn ($row) => [
                    'id' => (int) $row->id,
                    'nombre' => (string) $row->nombre,
                    'total' => (float) $row->total,
                ])
                ->all();

            return [$turno, ['desglose_pagos' => $desglosePagos]];
        });
    }

    /**
     * Retorna el turno activo del usuario autenticado (o null si no hay).
     * Calcula los acumulados desde MovimientoCaja en cada llamada.
     *
     * @return array{Turno|null, array{ingresos_acumulados:float, saldo_esperado_actual:float, ventas_count:int, ventas_total:float, desglose_pagos:array<int,array{id:int,nombre:string,total:float}>}}
     */
    public static function getActivo(User $usuario): array
    {
        $turno = Turno::where('usuario_id', $usuario->id)
            ->where('estado', TurnoEstado::ABIERTO)
            ->orderByDesc('id')
            ->first();

        if (! $turno) {
            return [
                null,
                [
                    'ingresos_acumulados' => 0.0,
                    'saldo_esperado_actual' => 0.0,
                    'ventas_count' => 0,
                    'ventas_total' => 0.0,
                    'desglose_pagos' => [],
                ],
            ];
        }

        $ingresos = (float) MovimientoCaja::where('usuario_id', $usuario->id)
            ->where('fecha_movimiento', '>=', $turno->fecha_apertura)
            ->where('tipo', MovimientoCajaTipo::INGRESO->value)
            ->sum('monto');

        $ventasCount = (int) DB::table('ventas')
            ->where('usuario_id', $usuario->id)
            ->where('fecha', '>=', $turno->fecha_apertura)
            ->where('estado_pago', 'pagado')
            ->count();

        $ventasTotal = (float) DB::table('ventas')
            ->where('usuario_id', $usuario->id)
            ->where('fecha', '>=', $turno->fecha_apertura)
            ->where('estado_pago', 'pagado')
            ->sum('total');

        $desglosePagos = MovimientoCaja::query()
            ->selectRaw('formas_pago.id as id, formas_pago.nombre as nombre, COALESCE(SUM(movimientos_cajas.monto), 0) as total')
            ->from('formas_pago')
            ->leftJoin('movimientos_cajas', function ($join) use ($usuario, $turno) {
                $join->on('movimientos_cajas.forma_pago_id', '=', 'formas_pago.id')
                    ->where('movimientos_cajas.usuario_id', '=', $usuario->id)
                    ->where('movimientos_cajas.fecha_movimiento', '>=', $turno->fecha_apertura)
                    ->where('movimientos_cajas.tipo', '=', MovimientoCajaTipo::INGRESO->value);
            })
            ->where('formas_pago.activo', true)
            ->groupBy('formas_pago.id', 'formas_pago.nombre')
            ->orderBy('formas_pago.id')
            ->get()
            ->map(fn ($row) => [
                'id' => (int) $row->id,
                'nombre' => (string) $row->nombre,
                'total' => (float) $row->total,
            ])
            ->all();

        return [
            $turno,
            [
                'ingresos_acumulados' => $ingresos,
                'saldo_esperado_actual' => (float) $turno->saldo_inicial + $ingresos,
                'ventas_count' => $ventasCount,
                'ventas_total' => $ventasTotal,
                'desglose_pagos' => $desglosePagos,
            ],
        ];
    }

    /**
     * Resumen de un turno arbitrario (útil para endpoint de cierre/resumen).
     *
     * @return array{
     *   ingresos_acumulados:float,
     *   saldo_esperado_actual:float,
     *   ventas_count:int,
     *   ventas_total:float,
     *   desglose_pagos:array<int,array{id:int,nombre:string,total:float}>
     * }
     */
    public static function resumen(Turno $turno): array
    {
        $ingresos = (float) MovimientoCaja::where('usuario_id', $turno->usuario_id)
            ->where('fecha_movimiento', '>=', $turno->fecha_apertura)
            ->where('tipo', MovimientoCajaTipo::INGRESO->value)
            ->sum('monto');

        $ventasCount = (int) DB::table('ventas')
            ->where('usuario_id', $turno->usuario_id)
            ->where('fecha', '>=', $turno->fecha_apertura)
            ->where('estado_pago', 'pagado')
            ->count();

        $ventasTotal = (float) DB::table('ventas')
            ->where('usuario_id', $turno->usuario_id)
            ->where('fecha', '>=', $turno->fecha_apertura)
            ->where('estado_pago', 'pagado')
            ->sum('total');

        $desglosePagos = MovimientoCaja::query()
            ->selectRaw('formas_pago.id as id, formas_pago.nombre as nombre, COALESCE(SUM(movimientos_cajas.monto), 0) as total')
            ->from('formas_pago')
            ->leftJoin('movimientos_cajas', function ($join) use ($turno) {
                $join->on('movimientos_cajas.forma_pago_id', '=', 'formas_pago.id')
                    ->where('movimientos_cajas.usuario_id', '=', $turno->usuario_id)
                    ->where('movimientos_cajas.fecha_movimiento', '>=', $turno->fecha_apertura)
                    ->where('movimientos_cajas.tipo', '=', MovimientoCajaTipo::INGRESO->value);
            })
            ->where('formas_pago.activo', true)
            ->groupBy('formas_pago.id', 'formas_pago.nombre')
            ->orderBy('formas_pago.id')
            ->get()
            ->map(fn ($row) => [
                'id' => (int) $row->id,
                'nombre' => (string) $row->nombre,
                'total' => (float) $row->total,
            ])
            ->all();

        return [
            'ingresos_acumulados' => $ingresos,
            'saldo_esperado_actual' => (float) $turno->saldo_inicial + $ingresos,
            'ventas_count' => $ventasCount,
            'ventas_total' => $ventasTotal,
            'desglose_pagos' => $desglosePagos,
        ];
    }

    /**
     * Retorna el turno abierto de una caja específica (cualquier usuario).
     * Útil para vistas de caja/admin. Devuelve null si la caja está libre.
     */
    public static function getActivoByCaja(Caja $caja): ?Turno
    {
        return Turno::where('caja_id', $caja->id)
            ->where('estado', TurnoEstado::ABIERTO)
            ->orderByDesc('id')
            ->first();
    }
}
