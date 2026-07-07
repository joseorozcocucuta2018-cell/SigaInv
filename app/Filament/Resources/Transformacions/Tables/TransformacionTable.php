<?php

declare(strict_types=1);

namespace App\Filament\Resources\Transformacions\Tables;

use App\Enums\TransformacionEstado;
use App\Enums\TransformacionTipo;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TransformacionTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('fecha')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('productoFinal.nombre')
                    ->label('Producto Final')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('cantidad_a_producir')
                    ->label('Cantidad')
                    ->numeric(0)
                    ->alignRight(),
                TextColumn::make('bodega.nombre')
                    ->label('Bodega')
                    ->sortable(),
                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->label() ?? $state)
                    ->color(fn ($state): string => $state?->color() ?? 'gray'),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state->label())
                    ->color(fn ($state): string => $state->color()),
            ])
            ->filters([
                SelectFilter::make('bodega_id')
                    ->label('Bodega')
                    ->relationship('bodega', 'nombre'),
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options(TransformacionEstado::class),
                SelectFilter::make('tipo')
                    ->label('Tipo')
                    ->options(TransformacionTipo::class),
            ])
            ->defaultPaginationPageOption(10)
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn ($record) => $record->estado?->value === TransformacionEstado::BORRADOR->value),
            ]);
    }
}
