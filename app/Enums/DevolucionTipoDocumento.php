<?php

declare(strict_types=1);

namespace App\Enums;

enum DevolucionTipoDocumento: string
{
    case VENTA = 'venta';
    case REMISION = 'remision';

    public function label(): string
    {
        return match ($this) {
            self::VENTA => 'Venta',
            self::REMISION => 'Remisión',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::VENTA => 'info',
            self::REMISION => 'success',
        };
    }
}
