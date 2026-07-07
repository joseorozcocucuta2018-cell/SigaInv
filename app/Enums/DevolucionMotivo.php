<?php

declare(strict_types=1);

namespace App\Enums;

enum DevolucionMotivo: string
{
    case CAMBIO = 'cambio';
    case DEFECTO = 'defecto';
    case ERROR_PEDIDO = 'error_pedido';
    case OTRO = 'otro';

    public function label(): string
    {
        return match ($this) {
            self::CAMBIO => 'Cambio',
            self::DEFECTO => 'Defecto',
            self::ERROR_PEDIDO => 'Error en Pedido',
            self::OTRO => 'Otro',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DEFECTO => 'danger',
            self::ERROR_PEDIDO => 'warning',
            self::CAMBIO => 'info',
            self::OTRO => 'gray',
        };
    }
}
