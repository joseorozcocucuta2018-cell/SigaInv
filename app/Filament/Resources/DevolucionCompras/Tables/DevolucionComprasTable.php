<?php

declare(strict_types=1);

namespace App\Filament\Resources\DevolucionCompras\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DevolucionComprasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero')
                    ->label('Número')
                    ->searchable(),
                TextColumn::make('compra.numero')
                    ->label('Compra'),
                TextColumn::make('proveedor.nombre')
                    ->label('Proveedor'),
                TextColumn::make('fecha')
                    ->label('Fecha')
                    ->date('d/m/Y'),
                TextColumn::make('total')
                    ->label('Total')
                    ->currency(),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge(),
            ])
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(10);
    }
}
