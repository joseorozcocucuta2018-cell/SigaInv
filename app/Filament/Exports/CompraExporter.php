<?php

declare(strict_types=1);

namespace App\Filament\Exports;

use App\Models\Compra;
use App\Traits\FixesWindowsTempDirectory;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class CompraExporter extends Exporter
{
    use FixesWindowsTempDirectory;

    protected static ?string $model = Compra::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('numero')->label('Número'),
            ExportColumn::make('proveedor.nombre')->label('Proveedor'),
            ExportColumn::make('bodega.nombre')->label('Bodega'),
            ExportColumn::make('fecha')->label('Fecha'),
            ExportColumn::make('subtotal')->label('Subtotal'),
            ExportColumn::make('descuento')->label('Descuento'),
            ExportColumn::make('impuestos')->label('Impuestos'),
            ExportColumn::make('total')->label('Total'),
            ExportColumn::make('saldo_pendiente')->label('Saldo Pendiente'),
            ExportColumn::make('estado')->label('Estado'),
            ExportColumn::make('fecha_vencimiento')->label('Fecha Vencimiento'),
            ExportColumn::make('observaciones')->label('Observaciones'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $count = $export->successful_rows;

        return "Se exportaron {$count} compras correctamente.";
    }
}
