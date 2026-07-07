<?php

declare(strict_types=1);

namespace App\Filament\Resources\PagoClientes\Tables;

use App\Filament\Exports\PagoClienteExporter;
use App\Filament\Tables\CommonTableFilters;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PagoClientesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero')
                    ->label('Nro. Recibo')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('cliente.nombre')
                    ->label('Cliente')
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
                    ->label('Docs. Afectados')
                    ->counts('detalles')
                    ->badge()
                    ->color('info'),
            ])
            ->defaultSort('fecha', 'desc')
            ->filters([
                CommonTableFilters::cliente(),
                CommonTableFilters::formaPago(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                ExportAction::make()
                    ->label('Exportar')
                    ->exporter(PagoClienteExporter::class),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
