<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\CajaCategoria;
use App\Models\Banco;
use App\Models\Caja;
use App\Models\FormaPago;
use App\Models\MovimientoBanco;
use App\Models\MovimientoCaja;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CajaService
{
    /**
     * Crea una caja y registra el saldo inicial como movimiento
     */
    public static function crearCaja(array $data): Caja
    {
        $data['usuario_id'] = $data['usuario_id'] ?? Auth::id();

        return DB::transaction(function () use ($data) {
            $caja = Caja::create($data);

            $saldoInicial = (float) ($data['saldo_inicial'] ?? 0);

            if ($saldoInicial > 0) {
                $formaPago = FormaPago::where('nombre', 'Efectivo')->value('id');

                MovimientoCaja::create([
                    'caja_id' => $caja->id,
                    'usuario_id' => $caja->usuario_id,
                    'forma_pago_id' => $formaPago,
                    'fecha_movimiento' => now(),
                    'tipo' => 'ingreso',
                    'monto' => $saldoInicial,
                    'saldo_actual' => $saldoInicial,
                    'categoria' => 'saldo_inicial',
                    'concepto' => 'Saldo inicial de apertura',
                ]);
            }

            return $caja;
        });
    }

    /**
     * Registra un ingreso en caja
     */
    public static function registrarIngreso(
        int $cajaId,
        float $monto,
        string $concepto,
        CajaCategoria|string|null $categoria = 'ingreso_operativo',
        ?string $referencia = null,
        ?int $formaPagoId = null,
        ?string $documentoTipo = null,
        ?int $documentoId = null
    ): MovimientoCaja {
        $categoria = $categoria instanceof CajaCategoria ? $categoria->value : $categoria;

        return self::registrarMovimiento(
            $cajaId,
            'ingreso',
            $monto,
            $concepto,
            $categoria,
            $referencia,
            $formaPagoId,
            $documentoTipo,
            $documentoId
        );
    }

    /**
     * Registra un egreso en caja
     */
    public static function registrarEgreso(
        int $cajaId,
        float $monto,
        string $concepto,
        CajaCategoria|string|null $categoria = 'gasto_operativo',
        ?string $referencia = null,
        ?int $formaPagoId = null,
        ?string $documentoTipo = null,
        ?int $documentoId = null
    ): MovimientoCaja {
        $categoria = $categoria instanceof CajaCategoria ? $categoria->value : $categoria;

        return DB::transaction(function () use (
            $cajaId, $monto, $concepto, $categoria, $referencia, $formaPagoId, $documentoTipo, $documentoId
        ) {
            $caja = Caja::lockForUpdate()->findOrFail($cajaId);
            $saldoActual = $caja->saldo_actual;

            if ($monto > $saldoActual) {
                throw new InvalidArgumentException(
                    "Saldo insuficiente. Saldo actual: {$saldoActual}, Monto solicitado: {$monto}"
                );
            }

            return self::registrarMovimiento(
                $cajaId,
                'egreso',
                $monto,
                $concepto,
                $categoria,
                $referencia,
                $formaPagoId,
                $documentoTipo,
                $documentoId
            );
        });
    }

    /**
     * Traslada dinero de caja a otra caja
     */
    public static function trasladarACaja(
        int $origenCajaId,
        int $destinoCajaId,
        float $monto,
        ?string $concepto = null,
        ?string $referencia = null
    ): array {
        DB::beginTransaction();
        try {
            $cajaOrigen = Caja::lockForUpdate()->findOrFail($origenCajaId);
            $saldoOrigen = $cajaOrigen->saldo_actual;

            if ($monto > $saldoOrigen) {
                throw new InvalidArgumentException(
                    "Saldo insuficiente en caja origen. Saldo: {$saldoOrigen}, Monto: {$monto}"
                );
            }

            $concepto = $concepto ?? 'Traslado a caja';

            $movOrigen = self::registrarMovimiento(
                $origenCajaId,
                'traslado',
                $monto,
                $concepto,
                'traslado_caja',
                $referencia,
                null,
                null,
                null,
                'caja',
                $destinoCajaId
            );

            $cajaDestino = Caja::lockForUpdate()->findOrFail($destinoCajaId);
            $saldoDestino = $cajaDestino->saldo_actual;

            $movDestino = MovimientoCaja::create([
                'caja_id' => $destinoCajaId,
                'usuario_id' => Auth::id(),
                'forma_pago_id' => null,
                'fecha_movimiento' => now(),
                'tipo' => 'ingreso',
                'monto' => $monto,
                'saldo_actual' => $saldoDestino + $monto,
                'categoria' => 'traslado_caja',
                'referencia' => $referencia,
                'concepto' => 'Traslado desde otra caja',
                'documento_tipo' => 'movimiento_caja',
                'documento_id' => $movOrigen->id,
            ]);

            // Cross-reference: origen también apunta al destino
            $movOrigen->update([
                'documento_tipo' => 'movimiento_caja',
                'documento_id' => $movDestino->id,
            ]);

            DB::commit();

            return [
                'origen' => $movOrigen,
                'destino' => $movDestino,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Traslada dinero de caja a banco
     */
    public static function trasladarABanco(
        int $cajaId,
        int $bancoId,
        float $monto,
        ?string $concepto = null,
        ?string $referencia = null
    ): array {
        DB::beginTransaction();
        try {
            $caja = Caja::lockForUpdate()->findOrFail($cajaId);
            $saldoCaja = $caja->saldo_actual;

            if ($monto > $saldoCaja) {
                throw new InvalidArgumentException(
                    "Saldo insuficiente en caja. Saldo: {$saldoCaja}, Monto: {$monto}"
                );
            }

            // Egreso de caja
            $concepto = $concepto ?? 'Traslado a banco';

            $movCaja = self::registrarMovimiento(
                $cajaId,
                'traslado',
                $monto,
                $concepto,
                'traslado_banco',
                $referencia,
                null,
                null,
                null,
                'banco',
                $bancoId
            );

            // Depósito en banco (con lock para evitar race conditions)
            $banco = Banco::lockForUpdate()->findOrFail($bancoId);
            $saldoActual = $banco->saldo_actual;

            $movBanco = MovimientoBanco::create([
                'banco_id' => $bancoId,
                'usuario_id' => Auth::id(),
                'forma_pago_id' => null,
                'fecha_movimiento' => now(),
                'tipo' => 'deposito',
                'monto' => $monto,
                'saldo_actual' => $saldoActual + $monto,
                'referencia' => $referencia,
                'concepto' => 'Traslado desde caja',
                'documento_tipo' => 'movimiento_caja',
                'documento_id' => $movCaja->id,
            ]);

            // Cross-reference: movimiento de caja apunta al movimiento de banco
            $movCaja->update([
                'documento_tipo' => 'movimiento_banco',
                'documento_id' => $movBanco->id,
            ]);

            DB::commit();

            return [
                'movimiento_caja' => $movCaja,
                'movimiento_banco' => $movBanco,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Método interno para registrar movimiento
     */
    private static function registrarMovimiento(
        int $cajaId,
        string $tipo,
        float $monto,
        string $concepto,
        ?string $categoria = null,
        ?string $referencia = null,
        ?int $formaPagoId = null,
        ?string $documentoTipo = null,
        ?int $documentoId = null,
        ?string $trasladoDestinoTipo = null,
        ?int $trasladoDestinoId = null
    ): MovimientoCaja {
        $caja = Caja::lockForUpdate()->findOrFail($cajaId);
        $saldoActual = $caja->saldo_actual;

        $nuevoSaldo = in_array($tipo, ['egreso', 'traslado'])
            ? $saldoActual - $monto
            : $saldoActual + $monto;

        return MovimientoCaja::create([
            'caja_id' => $cajaId,
            'usuario_id' => Auth::id(),
            'forma_pago_id' => $formaPagoId,
            'fecha_movimiento' => now(),
            'tipo' => $tipo,
            'monto' => $monto,
            'saldo_actual' => $nuevoSaldo,
            'categoria' => $categoria,
            'referencia' => $referencia,
            'concepto' => $concepto,
            'traslado_destino_tipo' => $trasladoDestinoTipo,
            'traslado_destino_id' => $trasladoDestinoId,
            'documento_tipo' => $documentoTipo,
            'documento_id' => $documentoId,
        ]);
    }

    /**
     * Valida que haya saldo suficiente en caja
     */
    public static function validarSaldoSuficiente(int $cajaId, float $monto): bool
    {
        $caja = Caja::findOrFail($cajaId);

        return $caja->saldo_actual >= $monto;
    }

    /**
     * Obtiene el saldo actual de una caja
     */
    public static function getSaldoActual(int $cajaId): float
    {
        $caja = Caja::findOrFail($cajaId);

        return $caja->saldo_actual;
    }
}
