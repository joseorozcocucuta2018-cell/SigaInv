<?php

declare(strict_types=1);

namespace App\Enums;

enum NumeracionEstado: string
{
    case ACTIVO = 'activo';
    case INACTIVO = 'inactivo';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVO => 'Activo',
            self::INACTIVO => 'Inactivo',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ACTIVO => 'success',
            self::INACTIVO => 'danger',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::ACTIVO => 'heroicon-m-check-circle',
            self::INACTIVO => 'heroicon-m-no-symbol',
        };
    }
}
