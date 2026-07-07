<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProveedorResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductosCompradosRelationManager extends RelationManager
{
    protected static string $relationship = 'detallesCompra';

    protected static ?string $title = 'Productos Comprados';

    protected static ?string $modelLabel = 'Producto';

    protected static ?string $pluralModelLabel = 'Productos';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('producto.nombre')
                    ->label('Producto')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('compra.numero')
                    ->label('Compra')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('cantidad')
                    ->label('Cant.')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                TextColumn::make('precio_unitario')
                    ->label('Precio Unit.')
                    ->currency()
                    ->sortable(),
                TextColumn::make('subtotal')
                    ->label('Total Línea')
                    ->currency()
                    ->sortable(),
                TextColumn::make('compra.fecha')
                    ->label('Fecha Compra')
                    ->date()
                    ->sortable(),
            ])
            ->defaultSort('compra.fecha', 'desc')
            ->filters([
                //
            ])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
