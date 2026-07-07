<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Tipos de promociones
 *
 * - DESCUENTO: Descuento sobre el precio de ciertos productos (ej: 20% off)
 * - CANTIDAD: Pague 2 y lleve 3, Lleve 10 y pague 8
 * - EMPAQUETADO: Múltiples productos -> 1 producto nuevo (ej: Kit Celular)
 */
enum TipoPromo: string
{
    case DESCUENTO = 'descuento';
    case CANTIDAD = 'cantidad';
    case EMPAQUETADO = 'empaquetado';

    /**
     * Obtiene la etiqueta legible del tipo de promo
     */
    public function label(): string
    {
        return match ($this) {
            self::DESCUENTO => 'Descuento',
            self::CANTIDAD => 'Por Cantidad',
            self::EMPAQUETADO => 'Empaquetado',
        };
    }

    /**
     * Obtiene la descripción del tipo de promo
     */
    public function description(): string
    {
        return match ($this) {
            self::DESCUENTO => 'Aplicar descuento sobre el precio original',
            self::CANTIDAD => 'Pague X productos y lleve Y (promo por cantidad)',
            self::EMPAQUETADO => 'Crear un nuevo producto pack/combo',
        };
    }

    /**
     * Obtiene el color para UI
     */
    public function color(): string
    {
        return match ($this) {
            self::DESCUENTO => 'danger',
            self::CANTIDAD => 'warning',
            self::EMPAQUETADO => 'info',
        };
    }

    /**
     * Verifica si crea stock nuevo
     * Las promos de descuento no crean stock, solo afectan precios
     */
    public function creaStock(): bool
    {
        return in_array($this, [self::CANTIDAD, self::EMPAQUETADO]);
    }

    /**
     * Verifica si requiere fecha de vencimiento
     * Las promos por cantidad y empaquetado sí requieren
     */
    public function requiereVencimiento(): bool
    {
        return in_array($this, [self::CANTIDAD, self::EMPAQUETADO]);
    }
}
