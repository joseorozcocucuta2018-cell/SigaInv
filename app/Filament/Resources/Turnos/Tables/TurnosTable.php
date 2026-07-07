<?php

declare(strict_types=1);

namespace App\Filament\Resources\Turnos\Tables;

use App\Enums\TurnoEstado;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TurnosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('caja.nombre')
                    ->label('Caja')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('usuario.name')
                    ->label('Usuario')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('fecha_apertura')
                    ->label('Apertura')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('fecha_cierre')
                    ->label('Cierre')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('saldo_inicial')
                    ->label('Saldo Inicial')
                    ->currency(),
                TextColumn::make('saldo_final_esperado')
                    ->label('Esperado')
                    ->currency(),
                TextColumn::make('saldo_final_real')
                    ->label('Real')
                    ->currency(),
                TextColumn::make('diferencia')
                    ->label('Diferencia')
                    ->currency()
                    ->color(fn (?string $state): string => $state && (float) $state !== 0.0 ? 'danger' : 'success'),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (TurnoEstado $state): string => $state->label())
                    ->color(fn (TurnoEstado $state): string => $state->color()),
            ])
            ->defaultSort('fecha_apertura', 'desc')
            ->recordActions([])
            ->toolbarActions([]);
    }
}
