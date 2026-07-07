<?php

declare(strict_types=1);

namespace App\Enums;

enum CajaEstado: string
{
    case ACTIVA = 'activa';
    case INACTIVA = 'inactiva';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVA => 'Activa',
            self::INACTIVA => 'Inactiva',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ACTIVA => 'success',
            self::INACTIVA => 'danger',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::ACTIVA => 'heroicon-m-check-circle',
            self::INACTIVA => 'heroicon-m-no-symbol',
        };
    }
}
