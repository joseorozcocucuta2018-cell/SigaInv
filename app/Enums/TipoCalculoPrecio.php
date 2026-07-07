<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Modo de cálculo del precio sugerido en fórmulas de transformación.
 *
 * - `margen`: el usuario define el margen deseado y el sistema calcula el precio.
 * - `manual`: el usuario define el precio directamente y el sistema calcula el margen resultante.
 */
enum TipoCalculoPrecio: string
{
    case MARGEN = 'margen';
    case MANUAL = 'manual';

    public function label(): string
    {
        return match ($this) {
            self::MARGEN => 'Margen de ganancia',
            self::MANUAL => 'Precio manual',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::MARGEN => 'Ingrese el % de margen deseado y el sistema calculará el precio sugerido',
            self::MANUAL => 'Ingrese el precio de venta directamente y el sistema calculará el margen resultante',
        };
    }

    public static function defaultForTipo(TransformacionTipo $tipo): self
    {
        return match ($tipo) {
            TransformacionTipo::FABRICACION => self::MARGEN,
            TransformacionTipo::REENVASE => self::MANUAL,
            TransformacionTipo::COMBO => self::MANUAL,
            TransformacionTipo::PROMO => self::MANUAL,
        };
    }

    public static function defaultMargenForTipo(TransformacionTipo $tipo): float
    {
        return match ($tipo) {
            TransformacionTipo::FABRICACION => 30.0,
            TransformacionTipo::REENVASE => 45.0,
            TransformacionTipo::COMBO => 15.0,
            TransformacionTipo::PROMO => 10.0,
        };
    }
}
