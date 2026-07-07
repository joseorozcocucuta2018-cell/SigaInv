<?php

declare(strict_types=1);

namespace App\Filament\Resources\AuditoriaDocumentos\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AuditoriaDocumentosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha/Hora')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),
                TextColumn::make('usuario.name')
                    ->label('Usuario')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('documento_tipo')
                    ->label('Documento')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'venta' => 'Venta',
                        'compra' => 'Compra',
                        'remision' => 'Remisión',
                        'nota_credito' => 'Nota Crédito',
                        'nota_debito' => 'Nota Débito',
                        default => ucfirst($state),
                    }),
                TextColumn::make('documento_id')
                    ->label('ID Ref.')
                    ->searchable(),
                TextColumn::make('accion')
                    ->label('Acción')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'create' => 'success',
                        'update' => 'warning',
                        'confirm' => 'info',
                        'cancel' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('campo_modificado')
                    ->label('Campo')
                    ->placeholder('—'),
                TextColumn::make('valor_anterior')
                    ->label('Anterior')
                    ->limit(30)
                    ->placeholder('—'),
                TextColumn::make('valor_nuevo')
                    ->label('Nuevo')
                    ->limit(30)
                    ->placeholder('—'),
                TextColumn::make('ip_address')
                    ->label('IP')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('documento_tipo')
                    ->label('Tipo de Documento')
                    ->options([
                        'venta' => 'Ventas',
                        'compra' => 'Compras',
                        'remision' => 'Remisiones',
                        'nota_credito' => 'Notas Crédito',
                        'nota_debito' => 'Notas Débito',
                    ]),
                SelectFilter::make('accion')
                    ->label('Acción')
                    ->options([
                        'create' => 'Creación',
                        'update' => 'Modificación',
                        'confirm' => 'Confirmación',
                        'cancel' => 'Anulación',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(10);
    }
}
