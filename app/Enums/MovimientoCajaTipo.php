<?php

declare(strict_types=1);

namespace App\Enums;

enum MovimientoCajaTipo: string
{
    case INGRESO = 'ingreso';
    case EGRESO = 'egreso';
    case TRASLADO = 'traslado';
    case CONSIGNACION = 'consignacion';

    public function label(): string
    {
        return match ($this) {
            self::INGRESO => 'Ingreso',
            self::EGRESO => 'Egreso',
            self::TRASLADO => 'Traslado',
            self::CONSIGNACION => 'Consignación',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::INGRESO => 'success',
            self::EGRESO => 'danger',
            self::TRASLADO => 'info',
            self::CONSIGNACION => 'warning',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::INGRESO => 'heroicon-o-arrow-trending-up',
            self::EGRESO => 'heroicon-o-arrow-trending-down',
            self::TRASLADO => 'heroicon-o-arrows-right-left',
            self::CONSIGNACION => 'heroicon-o-building-bank',
        };
    }
}
