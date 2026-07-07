<?php

declare(strict_types=1);

namespace App\Enums;

enum MovimientoInventarioTipo: string
{
    case SALDO_INICIAL = 'saldo_inicial';
    case ENTRADA_COMPRA = 'entrada_compra';
    case SALIDA_VENTA = 'salida_venta';
    case SALIDA_REMISION = 'salida_remision';
    case ENTRADA_DEVOLUCION = 'entrada_devolucion';
    case SALIDA_DEVOLUCION = 'salida_devolucion';
    case TRASLADO_ENTRADA = 'traslado_entrada';
    case TRASLADO_SALIDA = 'traslado_salida';
    case SALIDA_TRASLADO = 'salida_traslado';
    case ENTRADA_TRASLADO = 'entrada_traslado';
    case REVERSO_TRASLADO = 'reverso_traslado';
    case AJUSTE_POSITIVO = 'ajuste_positivo';
    case AJUSTE_NEGATIVO = 'ajuste_negativo';
    case AJUSTE_COSTO_PROMEDIO = 'ajuste_costo_promedio';
    case AJUSTE_INICIAL = 'ajuste_inicial';
    case AJUSTE_CONTEO = 'ajuste_conteo';
    case REVERSO_ANULACION = 'reverso_anulacion';
    case FACTURACION_REMISION = 'facturacion_remision';
    case ANULACION_VENTA_REMISION = 'anulacion_venta_remision';
    case ENTRADA_TRANSFORMACION = 'entrada_transformacion';
    case SALIDA_TRANSFORMACION = 'salida_transformacion';
    case REVERSO_TRANSFORMACION = 'reverso_transformacion';

    public function label(): string
    {
        return match ($this) {
            self::SALDO_INICIAL => 'Saldo Inicial',
            self::ENTRADA_COMPRA => 'Entrada x Compra',
            self::SALIDA_VENTA => 'Salida x Venta',
            self::SALIDA_REMISION => 'Salida x Remisión',
            self::ENTRADA_DEVOLUCION => 'Entrada x Devolución',
            self::SALIDA_DEVOLUCION => 'Salida x Devolución',
            self::TRASLADO_ENTRADA => 'Traslado Entrada',
            self::TRASLADO_SALIDA => 'Traslado Salida',
            self::SALIDA_TRASLADO => 'Salida x Traslado',
            self::ENTRADA_TRASLADO => 'Entrada x Traslado',
            self::REVERSO_TRASLADO => 'Reverso Traslado',
            self::AJUSTE_POSITIVO => 'Ajuste Positivo',
            self::AJUSTE_NEGATIVO => 'Ajuste Negativo',
            self::AJUSTE_COSTO_PROMEDIO => 'Ajuste Costo Promedio',
            self::AJUSTE_INICIAL => 'Ajuste Inicial',
            self::AJUSTE_CONTEO => 'Ajuste x Conteo',
            self::REVERSO_ANULACION => 'Reverso x Anulación',
            self::FACTURACION_REMISION => 'Facturación Remisión',
            self::ANULACION_VENTA_REMISION => 'Anulación Venta/Remisión',
            self::ENTRADA_TRANSFORMACION => 'Entrada x Transformación',
            self::SALIDA_TRANSFORMACION => 'Salida x Transformación',
            self::REVERSO_TRANSFORMACION => 'Reverso Transformación',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::SALDO_INICIAL => 'gray',
            self::ENTRADA_COMPRA => 'success',
            self::SALIDA_VENTA => 'danger',
            self::SALIDA_REMISION => 'warning',
            self::ENTRADA_DEVOLUCION => 'info',
            self::SALIDA_DEVOLUCION => 'danger',
            self::TRASLADO_ENTRADA, self::ENTRADA_TRASLADO => 'info',
            self::TRASLADO_SALIDA, self::SALIDA_TRASLADO => 'warning',
            self::REVERSO_TRASLADO => 'gray',
            self::AJUSTE_POSITIVO => 'success',
            self::AJUSTE_NEGATIVO => 'danger',
            self::AJUSTE_COSTO_PROMEDIO => 'gray',
            self::AJUSTE_INICIAL => 'gray',
            self::AJUSTE_CONTEO => 'warning',
            self::REVERSO_ANULACION => 'info',
            self::FACTURACION_REMISION => 'primary',
            self::ANULACION_VENTA_REMISION => 'danger',
            self::ENTRADA_TRANSFORMACION => 'success',
            self::SALIDA_TRANSFORMACION => 'warning',
            self::REVERSO_TRANSFORMACION => 'gray',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::SALDO_INICIAL => 'heroicon-o-play',
            self::ENTRADA_COMPRA => 'heroicon-o-arrow-trending-up',
            self::SALIDA_VENTA => 'heroicon-o-arrow-trending-down',
            self::SALIDA_REMISION => 'heroicon-o-truck',
            self::ENTRADA_DEVOLUCION => 'heroicon-o-arrow-uturn-left',
            self::SALIDA_DEVOLUCION => 'heroicon-o-arrow-uturn-right',
            self::TRASLADO_ENTRADA, self::ENTRADA_TRASLADO => 'heroicon-o-arrow-right-end-on-rectangle',
            self::TRASLADO_SALIDA, self::SALIDA_TRASLADO => 'heroicon-o-arrow-right-start-on-rectangle',
            self::REVERSO_TRASLADO => 'heroicon-o-arrow-path',
            self::AJUSTE_POSITIVO => 'heroicon-o-plus-circle',
            self::AJUSTE_NEGATIVO => 'heroicon-o-minus-circle',
            self::AJUSTE_COSTO_PROMEDIO => 'heroicon-o-calculator',
            self::AJUSTE_INICIAL => 'heroicon-o-archive-box',
            self::AJUSTE_CONTEO => 'heroicon-o-clipboard-document-check',
            self::REVERSO_ANULACION => 'heroicon-o-backward',
            self::FACTURACION_REMISION => 'heroicon-o-document-text',
            self::ANULACION_VENTA_REMISION => 'heroicon-o-no-symbol',
            self::ENTRADA_TRANSFORMACION => 'heroicon-o-arrow-left-on-rectangle',
            self::SALIDA_TRANSFORMACION => 'heroicon-o-arrow-right-on-rectangle',
            self::REVERSO_TRANSFORMACION => 'heroicon-o-arrow-path-rounded-square',
        };
    }
}
