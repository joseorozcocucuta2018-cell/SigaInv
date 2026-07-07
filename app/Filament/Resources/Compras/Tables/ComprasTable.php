<?php

declare(strict_types=1);

namespace App\Filament\Resources\Compras\Tables;

use App\Enums\CompraEstado;
use App\Filament\Exports\CompraExporter;
use App\Filament\Tables\CommonTableFilters;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ComprasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero')
                    ->label('Nro. Factura')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('proveedor.nombre')
                    ->label('Proveedor')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                TextColumn::make('fecha')
                    ->label('Fecha')
                    ->dateTime('d/m/Y')
                    ->sortable(),
                TextColumn::make('total')
                    ->label('Total')
                    ->currency()
                    ->sortable(),
                TextColumn::make('saldo_pendiente')
                    ->label('Saldo')
                    ->currency()
                    ->sortable(),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (CompraEstado $state): string => $state->label())
                    ->color(fn (CompraEstado $state): string => $state->color()),
            ])
            ->defaultSort('fecha', 'desc')
            ->defaultPaginationPageOption(10)
            ->filters([
                CommonTableFilters::proveedor(),
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options(CompraEstado::class),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn ($record) => in_array($record->estado?->value, [
                        CompraEstado::BORRADOR->value,
                        CompraEstado::REGISTRADA->value,
                        CompraEstado::PENDIENTE->value,
                    ])),
                DeleteAction::make()->visible(fn ($record) => $record->estado?->value === CompraEstado::BORRADOR->value),
            ])
            ->toolbarActions([
                ExportAction::make()
                    ->label('Exportar')
                    ->exporter(CompraExporter::class),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
