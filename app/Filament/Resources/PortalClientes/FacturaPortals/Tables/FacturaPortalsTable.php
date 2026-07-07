<?php

declare(strict_types=1);

namespace App\Filament\Resources\PortalClientes\FacturaPortals\Tables;

use App\Enums\VentaEstado;
use App\Filament\Actions\PrintAction;
use App\Filament\Resources\PortalClientes\FacturaPortals\Schemas\FacturaPortalInfolist;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FacturaPortalsTable
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
                    ->currency()
                    ->sortable(),

                TextColumn::make('saldo_pendiente')
                    ->label('Saldo')
                    ->currency()
                    ->color(fn ($record) => $record->saldo_pendiente > 0 ? 'warning' : 'gray'),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (VentaEstado $state): string => match ($state) {
                        VentaEstado::CONFIRMADA => 'warning',
                        VentaEstado::PAGADA => 'success',
                        VentaEstado::ANULADA => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (VentaEstado $state): string => $state->label()),
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
                    ->modalHeading(fn ($record) => "Factura {$record->numero}")
                    ->schema(
                        fn ($record): array => FacturaPortalInfolist::configure(Schema::make())
                            ->getComponents()
                    ),
                PrintAction::make('pdf.venta'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }
}
