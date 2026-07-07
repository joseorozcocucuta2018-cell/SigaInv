<?php

declare(strict_types=1);

namespace App\Filament\Resources\FormulaTransformacions\Tables;

use App\Enums\TransformacionTipo;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class FormulaTransformacionTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('producto_final_nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (TransformacionTipo $state): string => $state->label())
                    ->color(fn (TransformacionTipo $state): string => $state->color()),
                TextColumn::make('productoFinal.nombre')
                    ->label('Producto Final')
                    ->searchable(),
                TextColumn::make('cantidad_producto_final')
                    ->label('Cantidad Final')
                    ->numeric(decimalPlaces: 3),
                BooleanColumn::make('activo')
                    ->label('Activo'),
                IconColumn::make('tiene_transformaciones')
                    ->label('En uso')
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-lock-open')
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->tooltip('Bloqueada porque ya tiene transformaciones ejecutadas'),
                BooleanColumn::make('bloqueada')
                    ->label('Bloq.')
                    ->trueColor('danger')
                    ->tooltip('Bloqueada manualmente'),
                TextColumn::make('detalles_count')
                    ->label('Componentes')
                    ->counts('detalles')
                    ->numeric(),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('activo')
                    ->label('Activas'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->visible(fn ($record) => $record->tiene_transformaciones || $record->bloqueada),
                EditAction::make()
                    ->visible(fn ($record) => ! $record->tiene_transformaciones && ! $record->bloqueada),
                DeleteAction::make()
                    ->visible(fn ($record) => ! $record->tiene_transformaciones
                        && ! $record->bloqueada
                        && Auth::user()?->hasRole('administrador')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ])
            ->paginated([10, 25, 50, 100]);
    }
}
