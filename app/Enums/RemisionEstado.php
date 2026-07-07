<?php

declare(strict_types=1);

namespace App\Enums;

enum RemisionEstado: string
{
    case BORRADOR = 'borrador';
    case CONFIRMADA = 'confirmada';
    case FACTURADA = 'facturada';
    case ANULADA = 'anulada';

    /**
     * Obtiene la etiqueta legible del estado
     */
    public function label(): string
    {
        return match ($this) {
            self::BORRADOR => 'Borrador',
            self::CONFIRMADA => 'Confirmada',
            self::FACTURADA => 'Facturada',
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
            self::FACTURADA => 'success',
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
            self::FACTURADA => 'heroicon-m-document-check',
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
        return in_array($this, [self::FACTURADA, self::ANULADA]);
    }

    /**
     * Obtiene los estados a los que se puede transicionar
     */
    public function validTransitions(): array
    {
        return match ($this) {
            self::BORRADOR => [self::CONFIRMADA, self::ANULADA],
            self::CONFIRMADA => [self::FACTURADA, self::ANULADA],
            self::FACTURADA => [],
            self::ANULADA => [],
        };
    }
}
