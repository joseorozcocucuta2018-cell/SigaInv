<?php

declare(strict_types=1);

namespace App\Filament\Resources\StockBodegas\Tables;

use App\Models\StockBodega;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StockBodegasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('producto.nombre')
                    ->label('Producto')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('bodega.nombre')
                    ->label('Bodega')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('cantidad')
                    ->label('Cantidad')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('producto_id')
                    ->label('Producto')
                    ->relationship('producto', 'nombre')
                    ->preload()
                    ->searchable(),
                SelectFilter::make('bodega_id')
                    ->label('Bodega')
                    ->relationship('bodega', 'nombre')
                    ->preload()
                    ->searchable(),
                SelectFilter::make('categoria_id')
                    ->label('Categoría')
                    ->relationship('producto.categoria', 'nombre')
                    ->preload()
                    ->searchable(),
                SelectFilter::make('marca_id')
                    ->label('Marca')
                    ->relationship('producto.marca', 'nombre')
                    ->preload()
                    ->searchable(),
                SelectFilter::make('tipo_producto')
                    ->label('Tipo de Producto')
                    ->options([
                        'comprado' => 'Comprado',
                        'manufacturado' => 'Manufacturado',
                        'materia_prima' => 'Materia Prima',
                        'servicio' => 'Servicio',
                    ])
                    ->query(fn ($query, array $data) => $query->when(
                        $data['value'],
                        fn ($query, $value) => $query->whereHas('producto', fn ($query) => $query->where('tipo_producto', $value))
                    )),
                SelectFilter::make('ubicacion')
                    ->label('Ubicación')
                    ->options(fn () => StockBodega::query()->whereNotNull('ubicacion')->distinct()->pluck('ubicacion', 'ubicacion')->toArray())
                    ->searchable(),
            ])
            ->actions([])
            ->headerActions([]);
    }
}
