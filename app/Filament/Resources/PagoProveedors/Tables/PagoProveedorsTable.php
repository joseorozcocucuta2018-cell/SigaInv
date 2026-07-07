<?php

declare(strict_types=1);

namespace App\Filament\Resources\PagoProveedors\Tables;

use App\Filament\Exports\PagoProveedorExporter;
use App\Filament\Tables\CommonTableFilters;
use Filament\Actions\ExportAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PagoProveedorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero')
                    ->label('Nro. Egreso')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('proveedor.nombre')
                    ->label('Proveedor')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('fecha')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('monto')
                    ->label('Monto')
                    ->currency()
                    ->sortable(),
                TextColumn::make('formaPago.nombre')
                    ->label('Forma de Pago'),
                TextColumn::make('detalles_count')
                    ->label('Compras Afectadas')
                    ->counts('detalles')
                    ->badge()
                    ->color('info'),
            ])
            ->defaultSort('fecha', 'desc')
            ->filters([
                CommonTableFilters::proveedor(),
                CommonTableFilters::formaPago(),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                ExportAction::make()
                    ->label('Exportar')
                    ->exporter(PagoProveedorExporter::class),
            ]);
    }
}
