<?php

declare(strict_types=1);

namespace App\Filament\Resources\Auditorias\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AuditoriasTable
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
                    ->label('Tipo Doc.')
                    ->badge()
                    ->searchable(),
                TextColumn::make('accion')
                    ->label('Acción')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'create' => 'success',
                        'update' => 'warning',
                        'delete' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('documento_id')
                    ->label('ID Ref.')
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('accion')
                    ->label('Acción')
                    ->options([
                        'create' => 'Creación',
                        'update' => 'Actualización',
                        'delete' => 'Eliminación',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(10);
    }
}
