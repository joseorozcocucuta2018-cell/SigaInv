<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Estados de transformación de inventario
 *
 * - BORRADOR: Transformación en elaboración
 * - CONFIRMADA: Transformación ejecutada (stock modificado)
 * - REVERTIDA: Transformación revertida (solo para Combo y Promo)
 */
enum TransformacionEstado: string
{
    case BORRADOR = 'borrador';
    case CONFIRMADA = 'confirmada';
    case REVERTIDA = 'revertida';

    /**
     * Obtiene la etiqueta legible del estado
     */
    public function label(): string
    {
        return match ($this) {
            self::BORRADOR => 'Borrador',
            self::CONFIRMADA => 'Confirmada',
            self::REVERTIDA => 'Revertida',
        };
    }

    /**
     * Obtiene el color para UI
     */
    public function color(): string
    {
        return match ($this) {
            self::BORRADOR => 'gray',
            self::CONFIRMADA => 'success',
            self::REVERTIDA => 'warning',
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
            self::REVERTIDA => 'heroicon-m-arrow-uturn-left',
        };
    }

    /**
     * Verifica si el documento es editable
     * Solo puede editarsi está en estado borrador
     */
    public function isEditable(): bool
    {
        return $this === self::BORRADOR;
    }

    /**
     * Verifica si el documento está confirmado
     */
    public function isConfirmada(): bool
    {
        return $this === self::CONFIRMADA;
    }

    /**
     * Verifica si el documento está revertido
     */
    public function isRevertida(): bool
    {
        return $this === self::REVERTIDA;
    }

    /**
     * Verifica si es editable (puede cambiar a confirmado)
     */
    public function canConfirm(): bool
    {
        return $this === self::BORRADOR;
    }

    /**
     * Verifica si puede revertirse (solo Combo y Promo confirmados)
     */
    public function canRevert(): bool
    {
        return $this === self::CONFIRMADA;
    }
}
