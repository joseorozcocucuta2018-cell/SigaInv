<?php

declare(strict_types=1);

namespace App\Filament\Exports;

use App\Models\Cliente;
use App\Traits\FixesWindowsTempDirectory;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class ClienteExporter extends Exporter
{
    use FixesWindowsTempDirectory;

    protected static ?string $model = Cliente::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('nombre'),
            ExportColumn::make('tipo_documento'),
            ExportColumn::make('numero_documento'),
            ExportColumn::make('email'),
            ExportColumn::make('telefono'),
            ExportColumn::make('direccion'),
            ExportColumn::make('ciudad.nombre')->label('Ciudad'),
            ExportColumn::make('saldo'),
            ExportColumn::make('estado'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $count = $export->successful_rows;

        return "Se exportaron {$count} clientes correctamente.";
    }
}
