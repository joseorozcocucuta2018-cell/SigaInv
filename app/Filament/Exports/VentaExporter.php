<?php

declare(strict_types=1);

namespace App\Filament\Exports;

use App\Models\Venta;
use App\Traits\FixesWindowsTempDirectory;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class VentaExporter extends Exporter
{
    use FixesWindowsTempDirectory;

    protected static ?string $model = Venta::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('numero')->label('Número'),
            ExportColumn::make('cliente.nombre')->label('Cliente'),
            ExportColumn::make('bodega.nombre')->label('Bodega'),
            ExportColumn::make('fecha')->label('Fecha'),
            ExportColumn::make('subtotal')->label('Subtotal'),
            ExportColumn::make('descuento')->label('Descuento'),
            ExportColumn::make('impuestos')->label('Impuestos'),
            ExportColumn::make('total')->label('Total'),
            ExportColumn::make('saldo_pendiente')->label('Saldo Pendiente'),
            ExportColumn::make('estado_pago')->label('Estado de Pago'),
            ExportColumn::make('fecha_vencimiento')->label('Fecha Vencimiento'),
            ExportColumn::make('observaciones')->label('Observaciones'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $count = $export->successful_rows;

        return "Se exportaron {$count} ventas correctamente.";
    }
}
