<?php

declare(strict_types=1);

namespace App\Enums;

enum CajaTipo: string
{
    case GENERAL = 'caja_general';
    case MENOR = 'caja_menor';
    case SUCURSAL = 'caja_sucursal';
    case POS = 'caja_pos';

    public function label(): string
    {
        return match ($this) {
            self::GENERAL => 'Caja General',
            self::MENOR => 'Caja Menor',
            self::SUCURSAL => 'Caja Sucursal',
            self::POS => 'Caja POS',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::GENERAL => 'success',
            self::MENOR => 'warning',
            self::SUCURSAL => 'info',
            self::POS => 'primary',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::GENERAL => 'heroicon-o-building-library',
            self::MENOR => 'heroicon-o-wallet',
            self::SUCURSAL => 'heroicon-o-building-office',
            self::POS => 'heroicon-o-shopping-cart',
        };
    }
}
