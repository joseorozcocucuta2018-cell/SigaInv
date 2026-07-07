<?php

declare(strict_types=1);

namespace App\Filament\Resources\PortalClientes\CotizacionPortals\Tables;

use App\Enums\CotizacionEstado;
use App\Filament\Actions\PrintAction;
use App\Filament\Resources\PortalClientes\CotizacionPortals\Schemas\CotizacionPortalInfolist;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CotizacionPortalsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero')
                    ->label('Número')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('fecha')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('total')
                    ->label('Total')
                    ->money('COP', divideBy: 1)
                    ->sortable(),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (CotizacionEstado $state): string => match ($state) {
                        CotizacionEstado::PENDIENTE => 'warning',
                        CotizacionEstado::ACEPTADA => 'success',
                        CotizacionEstado::RECHAZADA => 'danger',
                        CotizacionEstado::VENCIDA => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (CotizacionEstado $state): string => $state->label()),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('ver')
                    ->label('Ver')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar')
                    ->modalHeading(fn ($record) => "Cotización {$record->numero}")
                    ->schema(
                        fn ($record): array => CotizacionPortalInfolist::configure(Schema::make())
                            ->getComponents()
                    ),
                PrintAction::make('pdf.cotizacion'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }
}
