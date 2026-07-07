<?php

declare(strict_types=1);

namespace App\Filament\Resources\StockBodegas\Pages;

use App\Filament\Resources\StockBodegas\StockBodegaResource;
use Filament\Resources\Pages\ListRecords;
use pxlrbt\FilamentExcel\Actions\Pages\ExportAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class ListStockBodegas extends ListRecords
{
    protected static string $resource = StockBodegaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make()
                ->label('Excel')
                ->color('success')
                ->exports([
                    ExcelExport::make()
                        ->fromModel()
                        ->withFilename('stock-bodegas-completo-'.date('Y-m-d'))
                        ->withColumns([
                            Column::make('producto.codigo')->heading('Código'),
                            Column::make('producto.nombre')->heading('Producto'),
                            Column::make('producto.tipo_producto')->heading('Tipo'),
                            Column::make('producto.categoria.nombre')->heading('Categoría'),
                            Column::make('producto.marca.nombre')->heading('Marca'),
                            Column::make('bodega.nombre')->heading('Bodega'),
                            Column::make('cantidad')->heading('Cantidad'),
                            Column::make('ubicacion')->heading('Ubicación'),
                            Column::make('producto.precio_compra')->heading('Precio Compra'),
                            Column::make('producto.costo_promedio')->heading('Costo Promedio'),
                            Column::make('producto.precio_venta')->heading('Precio Venta'),
                            Column::make('producto.stock_minimo')->heading('Stock Mínimo'),
                            Column::make('producto.stock_maximo')->heading('Stock Máximo'),
                            Column::make('created_at')->heading('Fecha Creación'),
                        ]),
                ]),
        ];
    }
}
