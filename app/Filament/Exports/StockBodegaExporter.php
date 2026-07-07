<?php

declare(strict_types=1);

namespace App\Filament\Exports;

use App\Models\StockBodega;
use App\Traits\FixesWindowsTempDirectory;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class StockBodegaExporter extends Exporter
{
    use FixesWindowsTempDirectory;

    protected static ?string $model = StockBodega::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('producto.codigo')->label('Código'),
            ExportColumn::make('producto.nombre')->label('Producto'),
            ExportColumn::make('producto.categoria.nombre')->label('Categoría'),
            ExportColumn::make('producto.marca.nombre')->label('Marca'),
            ExportColumn::make('bodega.nombre')->label('Bodega'),
            ExportColumn::make('cantidad'),
            ExportColumn::make('producto.stock_minimo')->label('Stock Mínimo'),
            ExportColumn::make('producto.precio_compra')->label('Precio Compra'),
            ExportColumn::make('producto.costo_promedio')->label('Costo Promedio'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $count = $export->successful_rows;

        return "Se exportaron {$count} registros de stock correctamente.";
    }
}
