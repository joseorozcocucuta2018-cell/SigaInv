<?php

declare(strict_types=1);

namespace App\Enums;

enum TurnoEstado: string
{
    case ABIERTO = 'abierto';
    case CERRADO = 'cerrado';

    public function label(): string
    {
        return match ($this) {
            self::ABIERTO => 'Abierto',
            self::CERRADO => 'Cerrado',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ABIERTO => 'success',
            self::CERRADO => 'gray',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::ABIERTO => 'heroicon-m-lock-open',
            self::CERRADO => 'heroicon-m-lock-closed',
        };
    }
}
