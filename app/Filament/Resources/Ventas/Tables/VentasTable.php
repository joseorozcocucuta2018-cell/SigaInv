<?php

declare(strict_types=1);

namespace App\Filament\Resources\Ventas\Tables;

use App\Enums\EstadoPagoEnum;
use App\Enums\VentaEstado;
use App\Filament\Actions\BulkResendEmailAction;
use App\Filament\Actions\PrintAction;
use App\Filament\Exports\VentaExporter;
use App\Filament\Tables\CommonTableFilters;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VentasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero')->label('Nro.')->searchable()->sortable(),
                TextColumn::make('cliente.nombre')->label('Cliente')->searchable()->sortable()->limit(30),
                TextColumn::make('fecha')->label('Fecha')->dateTime('d/m/Y')->sortable(),
                TextColumn::make('total')->label('Total')->currency()->sortable(),
                TextColumn::make('saldo_pendiente')->label('Saldo')->currency()->sortable(),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (VentaEstado $state): string => $state->label())
                    ->color(fn (VentaEstado $state): string => $state->color())
                    ->sortable(),
                TextColumn::make('estado_pago')
                    ->label('Pago')
                    ->badge()
                    ->formatStateUsing(fn (EstadoPagoEnum $state): string => $state->label())
                    ->color(fn (EstadoPagoEnum $state): string => $state->color()),
            ])
            ->defaultSort('fecha', 'desc')
            ->defaultPaginationPageOption(10)
            ->filters([
                CommonTableFilters::estadoPago(label: 'Estado Pago'),
            ])
            ->recordActions([
                PrintAction::make('pdf.venta'),
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()->visible(fn ($record) => $record->estado?->value === VentaEstado::BORRADOR->value),
            ])
            ->toolbarActions([
                ExportAction::make()
                    ->label('Exportar')
                    ->exporter(VentaExporter::class),
                BulkActionGroup::make([
                    BulkResendEmailAction::make('venta'),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
