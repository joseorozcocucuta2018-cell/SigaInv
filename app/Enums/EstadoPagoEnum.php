<?php

declare(strict_types=1);

namespace App\Enums;

enum EstadoPagoEnum: string
{
    case PENDIENTE = 'pendiente';
    case PARCIAL = 'parcial';
    case PAGADO = 'pagado';
    case ANULADA = 'anulada';

    public function label(): string
    {
        return match ($this) {
            self::PENDIENTE => 'Pendiente',
            self::PARCIAL => 'Parcial',
            self::PAGADO => 'Pagado',
            self::ANULADA => 'Anulada',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDIENTE => 'warning',
            self::PARCIAL => 'info',
            self::PAGADO => 'success',
            self::ANULADA => 'danger',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::PENDIENTE => 'heroicon-o-clock',
            self::PARCIAL => 'heroicon-o-arrow-path',
            self::PAGADO => 'heroicon-o-check-circle',
            self::ANULADA => 'heroicon-o-x-circle',
        };
    }
}
