<?php

declare(strict_types=1);

namespace App\Enums;

enum CompraEstado: string
{
    case BORRADOR = 'borrador';
    case REGISTRADA = 'registrada';
    case PENDIENTE = 'pendiente';
    case PAGADA = 'pagada';
    case ANULADA = 'anulada';

    public function label(): string
    {
        return match ($this) {
            self::BORRADOR => 'Borrador',
            self::REGISTRADA => 'Registrada',
            self::PENDIENTE => 'Pendiente',
            self::PAGADA => 'Pagada',
            self::ANULADA => 'Anulada',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::BORRADOR => 'gray',
            self::REGISTRADA => 'info',
            self::PENDIENTE => 'warning',
            self::PAGADA => 'success',
            self::ANULADA => 'danger',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::BORRADOR => 'heroicon-m-pencil',
            self::REGISTRADA => 'heroicon-m-check-circle',
            self::PENDIENTE => 'heroicon-m-clock',
            self::PAGADA => 'heroicon-m-banknotes',
            self::ANULADA => 'heroicon-m-x-circle',
        };
    }

    public function isEditable(): bool
    {
        return $this === self::BORRADOR;
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::PAGADA, self::ANULADA]);
    }

    public function validTransitions(): array
    {
        return match ($this) {
            self::BORRADOR => [self::REGISTRADA, self::ANULADA],
            self::REGISTRADA => [self::PENDIENTE, self::PAGADA, self::ANULADA],
            self::PENDIENTE => [self::PAGADA],
            self::PAGADA => [],
            self::ANULADA => [],
        };
    }
}
