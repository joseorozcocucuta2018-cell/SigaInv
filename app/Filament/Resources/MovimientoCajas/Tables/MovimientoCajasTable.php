<?php

declare(strict_types=1);

namespace App\Filament\Resources\MovimientoCajas\Tables;

use App\Enums\CajaCategoria;
use App\Enums\MovimientoCajaTipo;
use App\Filament\Tables\CommonTableFilters;
use App\Models\MovimientoCaja;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MovimientoCajasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('caja.nombre')
                    ->label('Caja')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('fecha_movimiento')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->formatStateUsing(fn (MovimientoCajaTipo $state): string => $state->label())
                    ->badge()
                    ->color(fn (MovimientoCajaTipo $state): string => $state->color()),
                TextColumn::make('monto')
                    ->label('Monto')
                    ->currency(),
                TextColumn::make('saldo_actual')
                    ->label('Saldo Resultante')
                    ->currency(),
                TextColumn::make('categoria')
                    ->label('Categoría')
                    ->formatStateUsing(fn (?string $state) => $state ? CajaCategoria::tryFrom($state)?->label() ?? $state : null)
                    ->searchable(),
                TextColumn::make('referencia')
                    ->label('Referencia')
                    ->searchable(),
                TextColumn::make('concepto')
                    ->label('Concepto')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('trasladoDestino')
                    ->label('Destino')
                    ->state(fn (MovimientoCaja $record) => match ($record->traslado_destino_tipo) {
                        'caja' => optional($record->trasladoDestino)->nombre,
                        'banco' => optional($record->trasladoDestino)->nombre,
                        default => null,
                    }),
                TextColumn::make('usuario.name')
                    ->label('Usuario'),
            ])
            ->defaultSort('fecha_movimiento', 'desc')
            ->filters([
                CommonTableFilters::caja(),
                CommonTableFilters::tipoMovimientoCaja(),
            ]);
    }
}
