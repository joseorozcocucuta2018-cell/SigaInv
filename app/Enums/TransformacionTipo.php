<?php

declare(strict_types=1);

namespace App\Enums;

use App\Models\Categoria;
use App\Models\Marca;

/**
 * Tipos de transformación de inventario
 *
 * - COMBO: Unión de productos existentes (ej: Celular + Funda + Audífono)
 * - PROMO: Promoción con descuento o por cantidad (pague 2 lleve 3)
 * - REENVASE: Cambiar empaque (Botella 1L → Botellas 100ml)
 * - FABRICACION: Crear producto nuevo a partir de materias primas
 */
enum TransformacionTipo: string
{
    case COMBO = 'combo';
    case PROMO = 'promo';
    case REENVASE = 'reenvase';
    case FABRICACION = 'fabricacion';

    /**
     * Obtiene la etiqueta legible del tipo
     */
    public function label(): string
    {
        return match ($this) {
            self::COMBO => 'Combo',
            self::PROMO => 'Promoción',
            self::REENVASE => 'Reenvase',
            self::FABRICACION => 'Fabricación',
        };
    }

    /**
     * Verifica si el tipo es reversible (puede revertirse)
     * Solo Combo y Promo pueden revertirse
     */
    public function reversible(): bool
    {
        return in_array($this, [self::COMBO, self::PROMO]);
    }

    /**
     * Verifica si es promoción
     */
    public function isPromo(): bool
    {
        return $this === self::PROMO;
    }

    /**
     * Obtiene el color para UI
     */
    public function color(): string
    {
        return match ($this) {
            self::COMBO => 'info',
            self::PROMO => 'warning',
            self::REENVASE => 'primary',
            self::FABRICACION => 'success',
        };
    }

    /**
     * Obtiene el ícono para UI
     */
    public function icon(): string
    {
        return match ($this) {
            self::COMBO => 'heroicon-o-cube',
            self::PROMO => 'heroicon-o-gift',
            self::REENVASE => 'heroicon-o-archive-box',
            self::FABRICACION => 'heroicon-o-beaker',
        };
    }

    /**
     * Categoría sugerida según el tipo de transformación.
     */
    public function categoriaSugerida(): ?Categoria
    {
        return Categoria::where('nombre', $this->label())
            ->where('activo', true)
            ->first();
    }

    public function marcaSugerida(): ?Marca
    {
        return Marca::where('nombre', $this->label())
            ->where('activo', true)
            ->first();
    }
}
