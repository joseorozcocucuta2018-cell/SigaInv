<?php

declare(strict_types=1);

namespace App\Enums;

enum TrasladoEstado: string
{
    case BORRADOR = 'borrador';
    case CONFIRMADA = 'confirmada';
    case REVERTIDA = 'revertida';
    case ANULADA = 'anulada';

    public function label(): string
    {
        return match ($this) {
            self::BORRADOR => 'Borrador',
            self::CONFIRMADA => 'Confirmada',
            self::REVERTIDA => 'Revertida',
            self::ANULADA => 'Anulada',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::BORRADOR => 'gray',
            self::CONFIRMADA => 'success',
            self::REVERTIDA => 'warning',
            self::ANULADA => 'danger',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::BORRADOR => 'heroicon-m-pencil',
            self::CONFIRMADA => 'heroicon-m-check-circle',
            self::REVERTIDA => 'heroicon-m-arrow-uturn-left',
            self::ANULADA => 'heroicon-m-x-circle',
        };
    }

    public function isEditable(): bool
    {
        return $this === self::BORRADOR;
    }
}
