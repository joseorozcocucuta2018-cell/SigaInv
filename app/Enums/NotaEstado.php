<?php

declare(strict_types=1);

namespace App\Enums;

enum NotaEstado: string
{
    case BORRADOR = 'borrador';
    case CONFIRMADA = 'confirmada';
    case ANULADA = 'anulada';

    public function label(): string
    {
        return match ($this) {
            self::BORRADOR => 'Borrador',
            self::CONFIRMADA => 'Confirmada',
            self::ANULADA => 'Anulada',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::BORRADOR => 'warning',
            self::CONFIRMADA => 'success',
            self::ANULADA => 'danger',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::BORRADOR => 'heroicon-o-document',
            self::CONFIRMADA => 'heroicon-o-check-circle',
            self::ANULADA => 'heroicon-o-x-circle',
        };
    }

    public function isEditable(): bool
    {
        return $this === self::BORRADOR;
    }
}
