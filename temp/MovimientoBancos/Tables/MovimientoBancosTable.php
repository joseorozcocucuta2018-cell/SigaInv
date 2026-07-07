<?php

namespace App\Filament\Resources\MovimientoBancos\Tables;

use App\Enums\MovimientoBancoTipo;
use App\Models\MovimientoBanco;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MovimientoBancosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('fecha_movimiento')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('banco.nombre_banco')
                    ->label('Banco')
                    ->sortable(),
                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => MovimientoBancoTipo::tryFrom($state)?->label() ?? $state)
                    ->color(fn (string $state): string => MovimientoBancoTipo::tryFrom($state)?->color() ?? 'gray'),
                TextColumn::make('monto')
                    ->label('Monto')
                    ->currency()
                    ->sortable(),
                TextColumn::make('saldo_actual')
                    ->label('Saldo Resultante')
                    ->currency(),
                TextColumn::make('referencia')
                    ->label('Ref.')
                    ->searchable(),
                TextColumn::make('concepto')
                    ->label('Concepto')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('trasladoDestino')
                    ->label('Destino')
                    ->state(fn (MovimientoBanco $record) => match ($record->traslado_destino_tipo) {
                        'caja' => optional($record->trasladoDestino)->nombre,
                        'banco' => optional($record->trasladoDestino)->nombre_banco,
                        default => null,
                    }),
            ])
            ->defaultSort('fecha_movimiento', 'desc')
            ->filters([
                SelectFilter::make('banco_id')
                    ->label('Banco')
                    ->relationship('banco', 'nombre_banco'),
                SelectFilter::make('tipo')
                    ->label('Tipo')
                    ->options([
                        'deposito' => 'Depósito / Entrada',
                        'retiro' => 'Retiro / Salida',
                        'transferencia' => 'Transferencia',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
