<?php

declare(strict_types=1);

namespace App\Filament\Resources\Devoluciones\Tables;

use App\Enums\DevolucionEstado;
use App\Filament\Exports\DevolucionExporter;
use App\Filament\Resources\Devoluciones\Actions\AnularDevolucion;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DevolucionesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero')
                    ->label('Devolución')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('tipo_documento')
                    ->label('Tipo')
                    ->badge()
                    ->sortable(),

                TextColumn::make('cliente.nombre')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('total')
                    ->label('Total')
                    ->currency()
                    ->sortable(),

                TextColumn::make('motivo')
                    ->label('Motivo')
                    ->badge(),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn ($state): string => $state instanceof DevolucionEstado ? $state->color() : 'gray'),

                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('tipo_documento')
                    ->label('Tipo')
                    ->options([
                        'remision' => 'Remisión',
                        'venta' => 'Venta',
                    ]),

                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options(DevolucionEstado::class),

                SelectFilter::make('motivo')
                    ->label('Motivo')
                    ->options([
                        'cambio' => 'Cambio',
                        'defecto' => 'Defecto',
                        'error_pedido' => 'Error Pedido',
                        'otro' => 'Otro',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()->visible(fn ($record) => $record->estado?->value === DevolucionEstado::BORRADOR->value),
                AnularDevolucion::make(),
            ])
            ->toolbarActions([
                ExportAction::make()
                    ->label('Exportar')
                    ->exporter(DevolucionExporter::class),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
