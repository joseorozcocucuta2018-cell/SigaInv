<?php

declare(strict_types=1);

namespace App\Enums;

enum PagoMedioEnum: string
{
    case CAJA = 'caja';
    case BANCO = 'banco';

    public function label(): string
    {
        return match ($this) {
            self::CAJA => 'Caja (Efectivo)',
            self::BANCO => 'Banco (Transferencia)',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::CAJA => 'Caja',
            self::BANCO => 'Banco',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::CAJA => 'success',
            self::BANCO => 'info',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::CAJA => 'heroicon-o-banknotes',
            self::BANCO => 'heroicon-o-building-library',
        };
    }
}
