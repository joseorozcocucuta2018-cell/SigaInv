<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Banco;
use App\Models\Caja;
use App\Models\FormaPago;
use App\Models\MovimientoBanco;
use App\Models\MovimientoCaja;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class BancosService
{
    public static function crearBanco(array $data): Banco
    {
        $data['usuario_id'] = $data['usuario_id'] ?? Auth::id();

        return DB::transaction(function () use ($data) {
            $banco = Banco::create($data);

            $saldoInicial = (float) ($data['saldo_inicial'] ?? 0);

            if ($saldoInicial > 0) {
                $formaPago = FormaPago::where('nombre', 'Efectivo')->value('id');

                MovimientoBanco::create([
                    'banco_id' => $banco->id,
                    'usuario_id' => $banco->usuario_id,
                    'forma_pago_id' => $formaPago,
                    'fecha_movimiento' => now(),
                    'tipo' => 'deposito',
                    'monto' => $saldoInicial,
                    'saldo_actual' => $saldoInicial,
                    'concepto' => 'Saldo inicial de apertura',
                ]);
            }

            return $banco;
        });
    }

    public static function registrarDeposito(
        int $bancoId,
        float $monto,
        string $concepto,
        ?string $categoria = 'consignacion',
        ?string $referencia = null,
        ?int $formaPagoId = null,
        ?string $documentoTipo = null,
        ?int $documentoId = null
    ): MovimientoBanco {
        return self::registrarMovimiento(
            $bancoId,
            'deposito',
            $monto,
            $concepto,
            $categoria,
            $referencia,
            $formaPagoId,
            $documentoTipo,
            $documentoId
        );
    }

    public static function registrarRetiro(
        int $bancoId,
        float $monto,
        string $concepto,
        ?string $categoria = 'retiro',
        ?string $referencia = null,
        ?int $formaPagoId = null,
        ?string $documentoTipo = null,
        ?int $documentoId = null
    ): MovimientoBanco {
        return DB::transaction(function () use (
            $bancoId, $monto, $concepto, $categoria, $referencia, $formaPagoId, $documentoTipo, $documentoId
        ) {
            $banco = Banco::lockForUpdate()->findOrFail($bancoId);
            $saldoActual = $banco->saldo_actual;

            if ($monto > $saldoActual) {
                throw new InvalidArgumentException(
                    "Saldo insuficiente. Saldo actual: {$saldoActual}, Monto solicitado: {$monto}"
                );
            }

            return self::registrarMovimiento(
                $bancoId,
                'retiro',
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

    public static function registrarTransferenciaSaliente(
        int $origenBancoId,
        int $destinoBancoId,
        float $monto,
        ?string $concepto = null,
        ?string $referencia = null
    ): array {
        return DB::transaction(function () use ($origenBancoId, $destinoBancoId, $monto, $concepto, $referencia) {
            $bancoOrigen = Banco::lockForUpdate()->findOrFail($origenBancoId);
            $saldoOrigen = $bancoOrigen->saldo_actual;

            if ($monto > $saldoOrigen) {
                throw new InvalidArgumentException(
                    "Saldo insuficiente en banco origen. Saldo: {$saldoOrigen}, Monto: {$monto}"
                );
            }

            $concepto = $concepto ?? 'Transferencia entre cuentas';

            $movOrigen = self::registrarMovimiento(
                $origenBancoId,
                'transferencia',
                $monto,
                $concepto,
                'transferencia',
                $referencia,
                null,
                null,
                null,
                'banco',
                $destinoBancoId
            );

            $bancoDestino = Banco::lockForUpdate()->findOrFail($destinoBancoId);
            $saldoDestino = $bancoDestino->saldo_actual;

            $movDestino = MovimientoBanco::create([
                'banco_id' => $destinoBancoId,
                'usuario_id' => Auth::id(),
                'forma_pago_id' => null,
                'fecha_movimiento' => now(),
                'tipo' => 'deposito',
                'monto' => $monto,
                'saldo_actual' => $saldoDestino + $monto,
                'referencia' => $referencia,
                'concepto' => 'Transferencia recibida',
                'documento_tipo' => 'movimiento_banco',
                'documento_id' => $movOrigen->id,
            ]);

            // Cross-reference: origen también apunta al destino
            $movOrigen->update([
                'documento_tipo' => 'movimiento_banco',
                'documento_id' => $movDestino->id,
            ]);

            return [
                'origen' => $movOrigen,
                'destino' => $movDestino,
            ];
        });
    }

    public static function trasladarACaja(
        int $bancoId,
        int $cajaId,
        float $monto,
        ?string $concepto = null,
        ?string $referencia = null
    ): array {
        return DB::transaction(function () use ($bancoId, $cajaId, $monto, $concepto, $referencia) {
            $banco = Banco::lockForUpdate()->findOrFail($bancoId);
            $saldoBanco = $banco->saldo_actual;

            if ($monto > $saldoBanco) {
                throw new InvalidArgumentException("Saldo insuficiente en banco. Saldo: {$saldoBanco}, Monto: {$monto}");
            }

            $concepto = $concepto ?? 'Retiro a caja';
            $categoria = 'retiro';

            $movBanco = self::registrarMovimiento(
                $bancoId,
                'retiro',
                $monto,
                $concepto,
                $categoria,
                $referencia,
                null,
                null,
                null,
                'caja',
                $cajaId
            );

            $caja = Caja::lockForUpdate()->findOrFail($cajaId);
            $saldoCaja = $caja->saldo_actual;

            $movCaja = MovimientoCaja::create([
                'caja_id' => $cajaId,
                'usuario_id' => Auth::id(),
                'forma_pago_id' => null,
                'fecha_movimiento' => now(),
                'tipo' => 'traslado',
                'monto' => $monto,
                'saldo_actual' => $saldoCaja + $monto,
                'categoria' => null,
                'referencia' => $referencia,
                'concepto' => 'Retiro desde banco',
                'documento_tipo' => 'movimiento_banco',
                'documento_id' => $movBanco->id,
            ]);

            // Cross-reference: movimiento banco origen también apunta al destino caja
            $movBanco->update([
                'documento_tipo' => 'movimiento_caja',
                'documento_id' => $movCaja->id,
            ]);

            return [
                'movimiento_banco' => $movBanco,
                'movimiento_caja' => $movCaja,
            ];
        });
    }

    private static function registrarMovimiento(
        int $bancoId,
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
    ): MovimientoBanco {
        $banco = Banco::lockForUpdate()->findOrFail($bancoId);
        $saldoActual = $banco->saldo_actual;

        $nuevoSaldo = in_array($tipo, ['retiro', 'transferencia'])
            ? $saldoActual - $monto
            : $saldoActual + $monto;

        return MovimientoBanco::create([
            'banco_id' => $bancoId,
            'usuario_id' => Auth::id(),
            'forma_pago_id' => $formaPagoId,
            'fecha_movimiento' => now(),
            'tipo' => $tipo,
            'monto' => $monto,
            'saldo_actual' => $nuevoSaldo,
            'referencia' => $referencia,
            'concepto' => $concepto,
            'traslado_destino_tipo' => $trasladoDestinoTipo,
            'traslado_destino_id' => $trasladoDestinoId,
            'documento_tipo' => $documentoTipo,
            'documento_id' => $documentoId,
        ]);
    }

    public static function validarSaldoSuficiente(int $bancoId, float $monto): bool
    {
        $banco = Banco::findOrFail($bancoId);

        return $banco->saldo_actual >= $monto;
    }

    public static function getSaldoActual(int $bancoId): float
    {
        $banco = Banco::findOrFail($bancoId);

        return $banco->saldo_actual;
    }
}
