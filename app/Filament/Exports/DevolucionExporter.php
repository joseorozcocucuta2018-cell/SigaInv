<?php

declare(strict_types=1);

namespace App\Filament\Exports;

use App\Models\Devolucion;
use App\Traits\FixesWindowsTempDirectory;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class DevolucionExporter extends Exporter
{
    use FixesWindowsTempDirectory;

    protected static ?string $model = Devolucion::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('numero'),
            ExportColumn::make('tipo_documento')->label('Tipo'),
            ExportColumn::make('cliente.nombre')->label('Cliente'),
            ExportColumn::make('motivo'),
            ExportColumn::make('estado'),
            ExportColumn::make('subtotal'),
            ExportColumn::make('impuestos'),
            ExportColumn::make('total'),
            ExportColumn::make('created_at')->label('Fecha'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $count = $export->successful_rows;

        return "Se exportaron {$count} devoluciones correctamente.";
    }
}
