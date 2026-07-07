<?php

declare(strict_types=1);

namespace App\Enums;

enum AjusteEstado: string
{
    case BORRADOR = 'borrador';
    case CONFIRMADO = 'confirmado';
    case ANULADO = 'anulado';

    public function label(): string
    {
        return match ($this) {
            self::BORRADOR => 'Borrador',
            self::CONFIRMADO => 'Confirmado',
            self::ANULADO => 'Anulado',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::BORRADOR => 'gray',
            self::CONFIRMADO => 'success',
            self::ANULADO => 'danger',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::BORRADOR => 'heroicon-m-pencil',
            self::CONFIRMADO => 'heroicon-m-check-circle',
            self::ANULADO => 'heroicon-m-x-circle',
        };
    }

    public function isEditable(): bool
    {
        return $this === self::BORRADOR;
    }
}
