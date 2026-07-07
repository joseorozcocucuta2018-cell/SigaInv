<?php

declare(strict_types=1);

namespace App\Enums;

enum BancoTipoCuenta: string
{
    case AHORROS = 'ahorros';
    case CORRIENTE = 'corriente';

    public function label(): string
    {
        return match ($this) {
            self::AHORROS => 'Ahorros',
            self::CORRIENTE => 'Corriente',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::AHORROS => 'info',
            self::CORRIENTE => 'warning',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::AHORROS => 'heroicon-o-building-library',
            self::CORRIENTE => 'heroicon-o-building-bank',
        };
    }
}
