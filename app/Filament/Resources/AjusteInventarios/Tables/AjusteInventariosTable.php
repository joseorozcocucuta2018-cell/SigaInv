<?php

declare(strict_types=1);

namespace App\Filament\Resources\AjusteInventarios\Tables;

use App\Enums\AjusteEstado;
use App\Enums\MotivoAjuste;
use App\Filament\Tables\CommonTableFilters;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AjusteInventariosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero')
                    ->label('Número')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('fecha')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('bodega.nombre')
                    ->label('Bodega')
                    ->sortable(),

                TextColumn::make('motivo')
                    ->label('Motivo')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state instanceof MotivoAjuste ? $state->label() : $state)
                    ->color(fn ($state): string => $state instanceof MotivoAjuste ? $state->color() : 'gray'),

                TextColumn::make('detalles_count')
                    ->label('# Items')
                    ->counts('detalles')
                    ->sortable(),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state instanceof AjusteEstado ? $state->label() : $state)
                    ->color(fn ($state): string => $state instanceof AjusteEstado ? $state->color() : 'gray'),
            ])
            ->defaultSort('fecha', 'desc')
            ->defaultPaginationPageOption(10)
            ->filters([
                CommonTableFilters::motivoAjuste(),
                CommonTableFilters::bodega(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn ($record) => $record->estado?->value === AjusteEstado::BORRADOR->value),
            ]);
    }
}
