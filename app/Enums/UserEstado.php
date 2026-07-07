<?php

declare(strict_types=1);

namespace App\Enums;

enum UserEstado: string
{
    case PENDIENTE = 'pendiente';
    case ACTIVO = 'activo';
    case INACTIVO = 'inactivo';
    case BLOQUEADO = 'bloqueado';

    public function label(): string
    {
        return match ($this) {
            self::PENDIENTE => 'Pendiente',
            self::ACTIVO => 'Activo',
            self::INACTIVO => 'Inactivo',
            self::BLOQUEADO => 'Bloqueado',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDIENTE => 'warning',
            self::ACTIVO => 'success',
            self::INACTIVO => 'gray',
            self::BLOQUEADO => 'danger',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::PENDIENTE => 'heroicon-m-clock',
            self::ACTIVO => 'heroicon-m-check-circle',
            self::INACTIVO => 'heroicon-m-no-symbol',
            self::BLOQUEADO => 'heroicon-m-lock-closed',
        };
    }
}
