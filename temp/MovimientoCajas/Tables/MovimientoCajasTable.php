<?php

namespace App\Filament\Resources\MovimientoCajas\Tables;

use App\Enums\CajaCategoria;
use App\Models\MovimientoCaja;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
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
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'ingreso' => 'Ingreso',
                        'egreso' => 'Egreso',
                        'traslado' => 'Traslado',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'ingreso' => 'success',
                        'egreso' => 'danger',
                        'traslado' => 'info',
                        default => 'gray',
                    }),
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
                SelectFilter::make('caja_id')
                    ->label('Caja')
                    ->relationship('caja', 'nombre'),
                SelectFilter::make('tipo')
                    ->label('Tipo')
                    ->options([
                        'ingreso' => 'Ingreso',
                        'egreso' => 'Egreso',
                        'traslado' => 'Traslado',
                    ]),
            ]);
    }
}
