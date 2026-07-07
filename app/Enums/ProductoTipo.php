<?php

declare(strict_types=1);

namespace App\Enums;

enum ProductoTipo: string
{
    case COMPRADO = 'comprado';
    case MANUFACTURADO = 'manufacturado';
    case MATERIA_PRIMA = 'materia_prima';
    case SERVICIO = 'servicio';

    public function label(): string
    {
        return match ($this) {
            self::COMPRADO => 'Comprado',
            self::MANUFACTURADO => 'Manufacturado',
            self::MATERIA_PRIMA => 'Materia Prima',
            self::SERVICIO => 'Servicio',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::COMPRADO => 'info',
            self::MANUFACTURADO => 'warning',
            self::MATERIA_PRIMA => 'gray',
            self::SERVICIO => 'success',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::COMPRADO => 'heroicon-o-shopping-bag',
            self::MANUFACTURADO => 'heroicon-o-wrench-screwdriver',
            self::MATERIA_PRIMA => 'heroicon-o-cube',
            self::SERVICIO => 'heroicon-o-cog',
        };
    }
}
