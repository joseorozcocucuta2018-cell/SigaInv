<?php

declare(strict_types=1);

namespace App\Enums;

enum MovimientoSaldoClienteTipo: string
{
    case COMPRA = 'compra';
    case VENTA = 'venta';
    case DEVOLUCION = 'devolucion';
    case PAGO = 'pago';
    case AJUSTE = 'ajuste';

    public function label(): string
    {
        return match ($this) {
            self::COMPRA => 'Compra',
            self::VENTA => 'Venta',
            self::DEVOLUCION => 'Devolución',
            self::PAGO => 'Pago',
            self::AJUSTE => 'Ajuste',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::COMPRA => 'danger',
            self::VENTA => 'success',
            self::DEVOLUCION => 'warning',
            self::PAGO => 'info',
            self::AJUSTE => 'gray',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::COMPRA => 'heroicon-o-shopping-cart',
            self::VENTA => 'heroicon-o-credit-card',
            self::DEVOLUCION => 'heroicon-o-arrow-uturn-left',
            self::PAGO => 'heroicon-o-banknotes',
            self::AJUSTE => 'heroicon-o-adjustments-horizontal',
        };
    }
}
