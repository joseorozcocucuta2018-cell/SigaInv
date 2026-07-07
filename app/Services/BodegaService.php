<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Bodega;
use App\Models\Empresa;

class BodegaService
{
    /**
     * Retorna si la empresa usa una sola bodega.
     */
    public static function usaUnaSolaBodega(): bool
    {
        return Empresa::usaUnaSolaBodega();
    }

    /**
     * Retorna el ID de la bodega por defecto para formularios.
     * Si la empresa tiene una sola bodega, retorna esa.
     * Si no, retorna la bodega principal o 1 como fallback.
     */
    public static function bodegaDefaultId(): ?int
    {
        return Empresa::getBodegaPrincipalId() ?? Bodega::principal()?->id ?? 1;
    }

    /**
     * Retorna si el campo bodega_id debe estar deshabilitado en formularios.
     */
    public static function bodegaDeshabilitada(): bool
    {
        return self::usaUnaSolaBodega();
    }
}
