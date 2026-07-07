<?php

declare(strict_types=1);

namespace App\Filament\Resources\Remisions\Tables;

use App\Enums\EstadoPagoEnum;
use App\Enums\RemisionEstado;
use App\Filament\Actions\BulkResendEmailAction;
use App\Filament\Actions\PrintAction;
use App\Filament\Exports\RemisionExporter;
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

class RemisionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero')->label('Nro.')->searchable()->sortable(),
                TextColumn::make('cliente.nombre')->label('Cliente')->searchable()->sortable()->limit(30),
                TextColumn::make('fecha')->label('Fecha')->dateTime('d/m/Y')->sortable(),
                TextColumn::make('total')->label('Total')->currency()->sortable(),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (RemisionEstado $state): string => $state->label())
                    ->color(fn (RemisionEstado $state): string => $state->color()),
                TextColumn::make('estado_pago')
                    ->label('Pago')
                    ->badge()
                    ->formatStateUsing(fn (EstadoPagoEnum $state): string => $state->label())
                    ->color(fn (EstadoPagoEnum $state): string => $state->color()),
            ])
            ->defaultSort('fecha', 'desc')
            ->filters([
                SelectFilter::make('estado')
                    ->label('Documento')
                    ->options(RemisionEstado::class),
                CommonTableFilters::estadoPago(label: 'Pago'),
            ])
            ->recordActions([
                PrintAction::make('pdf.remision'),
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()->visible(fn ($record) => $record->estado?->value === RemisionEstado::BORRADOR->value),
            ])
            ->toolbarActions([
                ExportAction::make()
                    ->label('Exportar')
                    ->exporter(RemisionExporter::class),
                BulkActionGroup::make([
                    BulkResendEmailAction::make('remision'),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
