<?php

declare(strict_types=1);

namespace App\Filament\Exports;

use App\Models\PagoProveedor;
use App\Traits\FixesWindowsTempDirectory;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class PagoProveedorExporter extends Exporter
{
    use FixesWindowsTempDirectory;

    protected static ?string $model = PagoProveedor::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('numero')->label('Nro. Egreso'),
            ExportColumn::make('proveedor.nombre')->label('Proveedor'),
            ExportColumn::make('fecha'),
            ExportColumn::make('monto'),
            ExportColumn::make('formaPago.nombre')->label('Forma de Pago'),
            ExportColumn::make('referencia'),
            ExportColumn::make('observaciones'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $count = $export->successful_rows;

        return "Se exportaron {$count} pagos a proveedores correctamente.";
    }
}
