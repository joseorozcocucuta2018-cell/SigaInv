<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProveedorResource\RelationManagers;

use App\Enums\CompraEstado;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ComprasRelationManager extends RelationManager
{
    protected static string $relationship = 'compras';

    protected static ?string $title = 'Compras Realizadas';

    protected static ?string $modelLabel = 'Compra';

    protected static ?string $pluralModelLabel = 'Compras';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('numero')
            ->columns([
                TextColumn::make('numero')
                    ->label('Número')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('fecha')
                    ->label('Fecha')
                    ->date()
                    ->sortable(),
                TextColumn::make('total')
                    ->label('Total')
                    ->currency()
                    ->sortable(),
                TextColumn::make('saldo_pendiente')
                    ->label('Saldo Pendiente')
                    ->currency()
                    ->sortable(),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (CompraEstado $state): string => $state->label())
                    ->color(fn (CompraEstado $state): string => $state->color()),
            ])
            ->filters([
                //
            ])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
