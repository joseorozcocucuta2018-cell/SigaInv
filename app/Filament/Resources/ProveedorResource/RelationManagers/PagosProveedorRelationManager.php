<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProveedorResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PagosProveedorRelationManager extends RelationManager
{
    protected static string $relationship = 'pagos';

    protected static ?string $title = 'Pagos Realizados';

    protected static ?string $modelLabel = 'Pago';

    protected static ?string $pluralModelLabel = 'Pagos';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('numero')
            ->columns([
                TextColumn::make('numero')
                    ->label('Comprobante')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('fecha')
                    ->label('Fecha')
                    ->date()
                    ->sortable(),
                TextColumn::make('monto')
                    ->label('Monto')
                    ->currency()
                    ->sortable(),
                TextColumn::make('caja.nombre')
                    ->label('Caja')
                    ->placeholder('—'),
                TextColumn::make('banco.nombre_banco')
                    ->label('Banco')
                    ->placeholder('—'),
                TextColumn::make('formaPago.nombre')
                    ->label('Forma de Pago'),
                TextColumn::make('referencia')
                    ->label('Referencia')
                    ->placeholder('—'),
            ])
            ->filters([
                //
            ])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
