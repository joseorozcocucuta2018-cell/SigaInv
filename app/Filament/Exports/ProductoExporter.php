<?php

declare(strict_types=1);

namespace App\Filament\Exports;

use App\Models\Producto;
use App\Traits\FixesWindowsTempDirectory;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class ProductoExporter extends Exporter
{
    use FixesWindowsTempDirectory;

    protected static ?string $model = Producto::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('codigo')->label('Código'),
            ExportColumn::make('codigo_barras')->label('Cód. Barras'),
            ExportColumn::make('nombre')->label('Nombre'),
            ExportColumn::make('categoria.nombre')->label('Categoría'),
            ExportColumn::make('marca.nombre')->label('Marca'),
            ExportColumn::make('unidadMedida.nombre')->label('Unidad'),
            ExportColumn::make('precio_compra')->label('Precio Compra'),
            ExportColumn::make('precio_venta')->label('Precio Venta'),
            ExportColumn::make('stock_minimo')->label('Stock Mínimo'),
            ExportColumn::make('stock_maximo')->label('Stock Máximo'),
            ExportColumn::make('activo')->label('Activo'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $count = $export->successful_rows;

        return "Se exportaron {$count} productos correctamente.";
    }
}
