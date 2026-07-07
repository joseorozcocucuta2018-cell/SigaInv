<?php

declare(strict_types=1);

namespace App\Filament\Tables;

use App\Enums\CajaCategoria;
use App\Enums\DevolucionMotivo;
use App\Enums\DevolucionTipoDocumento;
use App\Enums\EstadoPagoEnum;
use App\Enums\MotivoAjuste;
use App\Enums\MovimientoBancoTipo;
use App\Enums\MovimientoCajaTipo;
use App\Enums\MovimientoInventarioTipo;
use App\Enums\NumeracionTipoDocumento;
use App\Enums\ProductoTipo;
use Filament\Tables\Filters\SelectFilter;

/**
 * Factory para SelectFilters comunes en tablas de Filament.
 * Centraliza los filtros de relación (con searchable+preload) y de enums.
 *
 * Uso:
 *   ->filters([
 *       CommonTableFilters::bodega(),
 *       CommonTableFilters::cliente(),
 *       CommonTableFilters::estadoPago(),
 *   ])
 */
final class CommonTableFilters
{
    public static function bodega(): SelectFilter
    {
        return self::relation('bodega_id', 'Bodega', 'bodega', 'nombre');
    }

    public static function cliente(): SelectFilter
    {
        return self::relation('cliente_id', 'Cliente', 'cliente', 'nombre');
    }

    public static function proveedor(): SelectFilter
    {
        return self::relation('proveedor_id', 'Proveedor', 'proveedor', 'nombre');
    }

    public static function formaPago(): SelectFilter
    {
        return self::relation('forma_pago_id', 'Forma de Pago', 'formaPago', 'nombre');
    }

    public static function categoria(): SelectFilter
    {
        return self::relation('categoria_id', 'Categoría', 'categoria', 'nombre');
    }

    public static function marca(): SelectFilter
    {
        return self::relation('marca_id', 'Marca', 'marca', 'nombre');
    }

    public static function caja(): SelectFilter
    {
        return self::relation('caja_id', 'Caja', 'caja', 'nombre');
    }

    public static function banco(): SelectFilter
    {
        return self::relation('banco_id', 'Banco', 'banco', 'nombre_banco');
    }

    public static function producto(): SelectFilter
    {
        return self::relation('producto_id', 'Producto', 'producto', 'nombre');
    }

    public static function estadoPago(string $label = 'Estado de Pago'): SelectFilter
    {
        return SelectFilter::make('estado_pago')
            ->label($label)
            ->options(EstadoPagoEnum::class);
    }

    public static function motivoAjuste(): SelectFilter
    {
        return SelectFilter::make('motivo')
            ->label('Motivo')
            ->options(MotivoAjuste::class);
    }

    public static function motivoDevolucion(): SelectFilter
    {
        return SelectFilter::make('motivo')
            ->label('Motivo')
            ->options(DevolucionMotivo::class);
    }

    public static function tipoDocumentoDevolucion(): SelectFilter
    {
        return SelectFilter::make('tipo_documento')
            ->label('Tipo de Documento')
            ->options(DevolucionTipoDocumento::class);
    }

    public static function tipoDocumentoNumeracion(): SelectFilter
    {
        return SelectFilter::make('tipo_documento')
            ->label('Tipo de Documento')
            ->options(NumeracionTipoDocumento::class);
    }

    public static function tipoMovimiento(): SelectFilter
    {
        return SelectFilter::make('tipo_movimiento')
            ->label('Tipo de Movimiento')
            ->options(MovimientoInventarioTipo::class);
    }

    public static function tipoMovimientoCaja(): SelectFilter
    {
        return SelectFilter::make('tipo')
            ->label('Tipo')
            ->options(MovimientoCajaTipo::class);
    }

    public static function tipoMovimientoBanco(): SelectFilter
    {
        return SelectFilter::make('tipo')
            ->label('Tipo')
            ->options(MovimientoBancoTipo::class);
    }

    public static function categoriaCaja(): SelectFilter
    {
        return SelectFilter::make('categoria')
            ->label('Categoría')
            ->options(CajaCategoria::class);
    }

    public static function tipoProducto(): SelectFilter
    {
        return SelectFilter::make('tipo_producto')
            ->label('Tipo de Producto')
            ->options(ProductoTipo::class);
    }

    /**
     * Helper genérico para filtros de relación con searchable + preload.
     * Usar solo si no hay un atajo específico arriba.
     */
    public static function relation(
        string $name,
        string $label,
        string $relationship,
        string $titleAttribute,
        bool $searchable = true,
        bool $preload = true,
    ): SelectFilter {
        $filter = SelectFilter::make($name)
            ->label($label)
            ->relationship($relationship, $titleAttribute);

        if ($searchable) {
            $filter->searchable();
        }
        if ($preload) {
            $filter->preload();
        }

        return $filter;
    }
}
