<?php

declare(strict_types=1);

namespace App\Filament\Resources\MovimientoInventarios\Tables;

use App\Enums\MovimientoInventarioTipo;
use App\Filament\Tables\CommonTableFilters;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MovimientoInventariosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('fecha_movimiento')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('producto.nombre')
                    ->label('Producto')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                TextColumn::make('bodega.nombre')
                    ->label('Bodega')
                    ->sortable(),
                TextColumn::make('tipo_movimiento')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (MovimientoInventarioTipo $state): string => $state->label())
                    ->color(fn (MovimientoInventarioTipo $state): string => $state->color()),
                TextColumn::make('cantidad')
                    ->label('Cant.')
                    ->numeric(3)
                    ->sortable(),
                TextColumn::make('stock_resultante')
                    ->label('Saldo')
                    ->numeric(3),
            ])
            ->defaultSort('fecha_movimiento', 'desc')
            ->defaultPaginationPageOption(10)
            ->filters([
                CommonTableFilters::tipoMovimiento(),
                CommonTableFilters::bodega(),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
