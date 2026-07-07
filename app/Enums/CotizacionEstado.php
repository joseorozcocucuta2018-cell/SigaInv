<?php

declare(strict_types=1);

namespace App\Enums;

enum CotizacionEstado: string
{
    case PENDIENTE = 'pendiente';
    case ENVIADA = 'enviada';
    case ACEPTADA = 'aceptada';
    case RECHAZADA = 'rechazada';
    case VENCIDA = 'vencida';

    public function label(): string
    {
        return match ($this) {
            self::PENDIENTE => 'Pendiente',
            self::ENVIADA => 'Enviada',
            self::ACEPTADA => 'Aceptada',
            self::RECHAZADA => 'Rechazada',
            self::VENCIDA => 'Vencida',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDIENTE => 'warning',
            self::ENVIADA => 'info',
            self::ACEPTADA => 'success',
            self::RECHAZADA => 'danger',
            self::VENCIDA => 'gray',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::PENDIENTE => 'heroicon-m-clock',
            self::ENVIADA => 'heroicon-m-paper-airplane',
            self::ACEPTADA => 'heroicon-m-check-circle',
            self::RECHAZADA => 'heroicon-m-x-circle',
            self::VENCIDA => 'heroicon-m-calendar',
        };
    }

    public function isEditable(): bool
    {
        return $this === self::PENDIENTE || $this === self::ENVIADA;
    }
}
