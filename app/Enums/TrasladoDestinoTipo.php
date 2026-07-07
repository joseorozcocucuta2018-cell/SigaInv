<?php

declare(strict_types=1);

namespace App\Enums;

enum TrasladoDestinoTipo: string
{
    case CAJA = 'caja';
    case BANCO = 'banco';

    public function label(): string
    {
        return match ($this) {
            self::CAJA => 'Otra Caja',
            self::BANCO => 'Cuenta de Banco',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::CAJA => 'warning',
            self::BANCO => 'info',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::CAJA => 'heroicon-o-building-library',
            self::BANCO => 'heroicon-o-building-bank',
        };
    }
}
