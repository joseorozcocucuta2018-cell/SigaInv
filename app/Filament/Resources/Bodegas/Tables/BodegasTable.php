<?php

declare(strict_types=1);

namespace App\Filament\Resources\Bodegas\Tables;

use App\Enums\BodegaEstado;
use App\Models\MovimientoInventario;
use App\Models\StockBodega;
use App\Models\Traslado;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class BodegasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('direccion1')
                    ->label('Dirección')
                    ->limit(40),
                TextColumn::make('ciudad.nombre')
                    ->label('Ciudad')
                    ->sortable(),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (BodegaEstado $state): string => $state->label())
                    ->color(fn (BodegaEstado $state): string => $state->color()),
            ])
            ->filters([])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
                    ->visible(fn ($record) => Auth::user()?->hasRole('administrador') && ! $record->es_principal)
                    ->before(function (DeleteAction $action, $record) {
                        if ($record->es_principal) {
                            Notification::make()
                                ->title('No se puede eliminar')
                                ->body('La bodega principal no puede ser eliminada.')
                                ->danger()->send();
                            $action->halt();

                            return;
                        }

                        $tieneMovimientos =
                            StockBodega::where('bodega_id', $record->id)->where('cantidad', '>', 0)->exists() ||
                            MovimientoInventario::where('bodega_id', $record->id)->exists() ||
                            Traslado::where('bodega_origen_id', $record->id)
                                ->orWhere('bodega_destino_id', $record->id)->exists();

                        if ($tieneMovimientos) {
                            Notification::make()
                                ->title('No se puede eliminar')
                                ->body('La bodega tiene stock o movimientos de inventario registrados.')
                                ->danger()->send();
                            $action->halt();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }
}
