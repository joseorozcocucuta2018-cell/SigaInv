<?php

declare(strict_types=1);

namespace App\Enums;

enum NumeracionTipoDocumento: string
{
    case VENTA = 'venta';
    case NOTA_CREDITO = 'nota_credito';
    case NOTA_DEBITO = 'nota_debito';
    case DOCUMENTO_EQUIVALENTE = 'documento_equivalente';
    case REMISION = 'remision';
    case COTIZACION = 'cotizacion';

    public function label(): string
    {
        return match ($this) {
            self::VENTA => 'Venta',
            self::NOTA_CREDITO => 'Nota Crédito',
            self::NOTA_DEBITO => 'Nota Débito',
            self::DOCUMENTO_EQUIVALENTE => 'Documento Equivalente',
            self::REMISION => 'Remisión',
            self::COTIZACION => 'Cotización',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::VENTA => 'success',
            self::NOTA_CREDITO => 'warning',
            self::NOTA_DEBITO => 'danger',
            self::DOCUMENTO_EQUIVALENTE => 'info',
            self::REMISION => 'primary',
            self::COTIZACION => 'gray',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::VENTA => 'heroicon-o-credit-card',
            self::NOTA_CREDITO => 'heroicon-o-arrow-up-circle',
            self::NOTA_DEBITO => 'heroicon-o-arrow-down-circle',
            self::DOCUMENTO_EQUIVALENTE => 'heroicon-o-document',
            self::REMISION => 'heroicon-o-truck',
            self::COTIZACION => 'heroicon-o-document-text',
        };
    }
}
