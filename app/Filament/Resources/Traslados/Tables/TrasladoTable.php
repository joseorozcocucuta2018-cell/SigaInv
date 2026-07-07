<?php

declare(strict_types=1);

namespace App\Filament\Resources\Traslados\Tables;

use App\Enums\TrasladoEstado;
use App\Filament\Resources\Traslados\Actions\AnularTraslado;
use App\Filament\Resources\Traslados\Actions\ConfirmarTraslado;
use App\Filament\Resources\Traslados\Actions\RevertirTraslado;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TrasladoTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Nro.')
                    ->sortable(),
                TextColumn::make('bodegaOrigen.nombre')
                    ->label('Bodega Origen')
                    ->searchable()
                    ->limit(25),
                TextColumn::make('bodegaDestino.nombre')
                    ->label('Bodega Destino')
                    ->searchable()
                    ->limit(25),
                TextColumn::make('fecha')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (TrasladoEstado $state): string => $state->color())
                    ->formatStateUsing(fn (TrasladoEstado $state): string => $state->label()),
                TextColumn::make('usuario.name')
                    ->label('Usuario')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([])
            ->recordActions([
                ConfirmarTraslado::make(),
                RevertirTraslado::make(),
                AnularTraslado::make(),
                EditAction::make()
                    ->visible(fn ($record) => $record->estado?->value === TrasladoEstado::BORRADOR->value),
                DeleteAction::make()->visible(fn ($record) => $record->estado?->value === TrasladoEstado::BORRADOR->value),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
