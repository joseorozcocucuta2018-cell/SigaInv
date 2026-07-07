<?php

declare(strict_types=1);

namespace App\Enums;

enum TransformacionLineaTipo: string
{
    case INSUMO = 'insumo';
    case PRODUCTO = 'producto';

    public function label(): string
    {
        return match ($this) {
            self::INSUMO => 'Insumo',
            self::PRODUCTO => 'Producto',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::INSUMO => 'warning',
            self::PRODUCTO => 'success',
        };
    }
}
