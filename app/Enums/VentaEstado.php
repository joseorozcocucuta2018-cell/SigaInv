<?php

declare(strict_types=1);

namespace App\Enums;

enum VentaEstado: string
{
    case BORRADOR = 'borrador';
    case CONFIRMADA = 'confirmada';
    case PAGADA = 'pagada';
    case ANULADA = 'anulada';

    /**
     * Obtiene la etiqueta legible del estado
     */
    public function label(): string
    {
        return match ($this) {
            self::BORRADOR => 'Borrador',
            self::CONFIRMADA => 'Confirmada',
            self::PAGADA => 'Pagada',
            self::ANULADA => 'Anulada',
        };
    }

    /**
     * Obtiene el color para UI
     */
    public function color(): string
    {
        return match ($this) {
            self::BORRADOR => 'gray',
            self::CONFIRMADA => 'info',
            self::PAGADA => 'success',
            self::ANULADA => 'danger',
        };
    }

    /**
     * Obtiene el ícono Heroicon para UI
     */
    public function icon(): string
    {
        return match ($this) {
            self::BORRADOR => 'heroicon-m-pencil',
            self::CONFIRMADA => 'heroicon-m-check-circle',
            self::PAGADA => 'heroicon-m-banknotes',
            self::ANULADA => 'heroicon-m-x-circle',
        };
    }

    /**
     * Verifica si el documento es editable
     */
    public function isEditable(): bool
    {
        return $this === self::BORRADOR;
    }

    /**
     * Verifica si el documento está finalizado (no puede cambiar de estado)
     */
    public function isFinal(): bool
    {
        return in_array($this, [self::PAGADA, self::ANULADA]);
    }

    /**
     * Obtiene los estados a los que se puede transicionar
     */
    public function validTransitions(): array
    {
        return match ($this) {
            self::BORRADOR => [self::CONFIRMADA, self::ANULADA],
            self::CONFIRMADA => [self::PAGADA, self::ANULADA],
            self::PAGADA => [],
            self::ANULADA => [],
        };
    }
}
