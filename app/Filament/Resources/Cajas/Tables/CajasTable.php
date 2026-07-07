<?php

declare(strict_types=1);

namespace App\Filament\Resources\Cajas\Tables;

use App\Enums\CajaEstado;
use App\Enums\CajaTipo;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CajasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (CajaTipo $state): string => $state->label())
                    ->color(fn (CajaTipo $state): string => $state->color())
                    ->sortable(),
                TextColumn::make('saldo_inicial')
                    ->label('Saldo Inicial')
                    ->currency(),
                TextColumn::make('saldo_actual')
                    ->label('Saldo Actual')
                    ->currency(),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (CajaEstado $state): string => $state->label())
                    ->color(fn (CajaEstado $state): string => $state->color()),
                TextColumn::make('usuario.name')
                    ->label('Creada por'),
            ])
            ->defaultSort('nombre')
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn ($record) => ! $record->tieneMovimientos()),
                DeleteAction::make()
                    ->visible(fn ($record) => ! $record->tieneMovimientos()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
