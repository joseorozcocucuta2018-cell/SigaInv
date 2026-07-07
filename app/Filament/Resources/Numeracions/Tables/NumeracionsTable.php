<?php

declare(strict_types=1);

namespace App\Filament\Resources\Numeracions\Tables;

use App\Enums\NumeracionEstado;
use App\Enums\NumeracionTipoDocumento;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class NumeracionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tipo_documento')
                    ->label('Tipo')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn (NumeracionTipoDocumento $state): string => $state->label())
                    ->color(fn (NumeracionTipoDocumento $state): string => $state->color()),
                TextColumn::make('prefijo')
                    ->label('Prefijo')
                    ->placeholder('—'),
                TextColumn::make('consecutivo_actual')
                    ->label('Actual')
                    ->sortable(),
                TextColumn::make('consecutivo_desde')
                    ->label('Desde'),
                TextColumn::make('consecutivo_hasta')
                    ->label('Hasta'),
                TextColumn::make('anno')
                    ->label('Año')
                    ->sortable(),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (NumeracionEstado $state): string => $state->label())
                    ->color(fn (NumeracionEstado $state): string => $state->color()),
            ])
            ->filters([
                SelectFilter::make('tipo_documento')
                    ->label('Tipo')
                    ->options(NumeracionTipoDocumento::class),
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options(NumeracionEstado::class),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
