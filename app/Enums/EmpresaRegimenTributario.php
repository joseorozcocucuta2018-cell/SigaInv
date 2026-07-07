<?php

declare(strict_types=1);

namespace App\Enums;

enum EmpresaRegimenTributario: string
{
    case SIMPLIFICADO = 'simplificado';
    case COMUN = 'comun';
    case GRAN_CONTRIBUYENTE = 'gran_contribuyente';

    public function label(): string
    {
        return match ($this) {
            self::SIMPLIFICADO => 'Simplificado',
            self::COMUN => 'Común',
            self::GRAN_CONTRIBUYENTE => 'Gran Contribuyente',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::SIMPLIFICADO => 'gray',
            self::COMUN => 'info',
            self::GRAN_CONTRIBUYENTE => 'warning',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::SIMPLIFICADO => 'heroicon-m-receipt-percent',
            self::COMUN => 'heroicon-m-document-text',
            self::GRAN_CONTRIBUYENTE => 'heroicon-m-star',
        };
    }

    public function responsableIva(): bool
    {
        return $this !== self::SIMPLIFICADO;
    }
}
