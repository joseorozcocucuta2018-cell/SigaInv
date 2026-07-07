<?php

declare(strict_types=1);

namespace App\Filament\Resources\ConteoFisicos\Tables;

use App\Enums\ConteoFisicoEstado;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ConteoFisicosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero')
                    ->label('Número')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('bodega.nombre')
                    ->label('Bodega')
                    ->searchable(),

                IconColumn::make('es_saldo_inicial')
                    ->label('Saldo Inicial')
                    ->boolean()
                    ->alignCenter(),

                TextColumn::make('fecha_inicio')
                    ->label('Fecha Inicio')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('fecha_cierre')
                    ->label('Fecha Cierre')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('detalles_count')
                    ->label('# Productos')
                    ->counts('detalles')
                    ->sortable(),

                TextColumn::make('diferencias_count')
                    ->label('# Diferencias')
                    ->getStateUsing(function ($record) {
                        return $record->detalles()
                            ->where('diferencia', '!=', 0)
                            ->whereNotNull('cantidad_contada')
                            ->count();
                    }),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state instanceof ConteoFisicoEstado ? $state->label() : $state)
                    ->color(fn ($state): string => $state instanceof ConteoFisicoEstado ? $state->color() : 'gray'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('bodega_id')
                    ->label('Bodega')
                    ->relationship('bodega', 'nombre'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn ($record) => $record->estado?->value === ConteoFisicoEstado::ABIERTO->value),
            ]);
    }
}
