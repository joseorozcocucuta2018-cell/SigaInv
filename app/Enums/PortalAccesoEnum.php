<?php

declare(strict_types=1);

namespace App\Enums;

enum PortalAccesoEnum: string
{
    case SIN_ACCESO = 'sin_acceso';
    case PENDIENTE = 'pendiente';
    case ACTIVO = 'activo';

    public function label(): string
    {
        return match ($this) {
            self::SIN_ACCESO => 'Sin Acceso',
            self::PENDIENTE => 'Pendiente',
            self::ACTIVO => 'Activo',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::SIN_ACCESO => 'gray',
            self::PENDIENTE => 'warning',
            self::ACTIVO => 'success',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::SIN_ACCESO => 'heroicon-m-lock-closed',
            self::PENDIENTE => 'heroicon-m-clock',
            self::ACTIVO => 'heroicon-m-check-circle',
        };
    }
}
