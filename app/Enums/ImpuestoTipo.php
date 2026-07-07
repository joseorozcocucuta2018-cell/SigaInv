<?php

declare(strict_types=1);

namespace App\Enums;

enum ImpuestoTipo: string
{
    case IVA = 'IVA';
    case INC = 'INC';
    case ICO = 'ICO';
    case RETE_IVA = 'ReteIVA';
    case RETE_ICA = 'ReteICA';
    case RETE_RENTA = 'ReteRenta';
    case OTRO = 'Otro';

    public function label(): string
    {
        return match ($this) {
            self::IVA => 'IVA',
            self::INC => 'INC',
            self::ICO => 'ICO',
            self::RETE_IVA => 'ReteIVA',
            self::RETE_ICA => 'ReteICA',
            self::RETE_RENTA => 'ReteRenta',
            self::OTRO => 'Otro',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::IVA => 'danger',
            self::INC => 'warning',
            self::ICO => 'info',
            self::RETE_IVA => 'primary',
            self::RETE_ICA => 'gray',
            self::RETE_RENTA => 'warning',
            self::OTRO => 'gray',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::IVA => 'heroicon-o-banknotes',
            self::INC => 'heroicon-o-document-text',
            self::ICO => 'heroiconO-document',
            self::RETE_IVA => 'heroicon-o-arrow-trending-down',
            self::RETE_ICA => 'heroicon-o-arrow-trending-down',
            self::RETE_RENTA => 'heroicon-o-arrow-trending-down',
            self::OTRO => 'heroicon-o-adjustments-horizontal',
        };
    }
}
