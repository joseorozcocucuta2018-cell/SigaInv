<?php

declare(strict_types=1);

namespace App\Enums;

enum CajaCategoria: string
{
    case SALDO_INICIAL = 'saldo_inicial';
    case VENTA = 'venta';
    case COMPRA = 'compra';
    case PAGO_CLIENTE = 'pago_cliente';
    case PAGO_PROVEEDOR = 'pago_proveedor';
    case GASTO_OPERATIVO = 'gasto_operativo';
    case NOMINA = 'nomina';
    case PRESTAMO = 'prestamo';
    case OTRO_INGRESO = 'otro_ingreso';
    case OTRO_EGRESO = 'otro_egreso';
    case TRASLADO_CAJA = 'traslado_caja';
    case TRASLADO_BANCO = 'traslado_banco';

    public function label(): string
    {
        return match ($this) {
            self::SALDO_INICIAL => 'Saldo Inicial',
            self::VENTA => 'Venta',
            self::COMPRA => 'Compra',
            self::PAGO_CLIENTE => 'Pago de Cliente',
            self::PAGO_PROVEEDOR => 'Pago a Proveedor',
            self::GASTO_OPERATIVO => 'Gasto Operativo',
            self::NOMINA => 'Nómina',
            self::PRESTAMO => 'Préstamo',
            self::OTRO_INGRESO => 'Otro Ingreso',
            self::OTRO_EGRESO => 'Otro Egreso',
            self::TRASLADO_CAJA => 'Traslado a Caja',
            self::TRASLADO_BANCO => 'Consignación a Banco',
        };
    }

    public function tipoMovimiento(): string
    {
        return match ($this) {
            self::SALDO_INICIAL, self::VENTA, self::PAGO_CLIENTE, self::PRESTAMO, self::OTRO_INGRESO => 'ingreso',
            self::COMPRA, self::PAGO_PROVEEDOR, self::GASTO_OPERATIVO, self::NOMINA, self::OTRO_EGRESO => 'egreso',
            self::TRASLADO_CAJA, self::TRASLADO_BANCO => 'traslado',
        };
    }
}
