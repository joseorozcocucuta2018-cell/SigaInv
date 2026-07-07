<?php

declare(strict_types=1);

namespace App\Filament\Resources\PortalClientes\RemisionPortals\Tables;

use App\Enums\RemisionEstado;
use App\Filament\Actions\PrintAction;
use App\Filament\Resources\PortalClientes\RemisionPortals\Schemas\RemisionPortalInfolist;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RemisionPortalsTable
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

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (RemisionEstado $state): string => match ($state) {
                        RemisionEstado::CONFIRMADA => 'info',
                        RemisionEstado::FACTURADA => 'success',
                        RemisionEstado::ANULADA => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (RemisionEstado $state): string => $state->label()),
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
                    ->modalHeading(fn ($record) => "Remisión {$record->numero}")
                    ->schema(
                        fn ($record): array => RemisionPortalInfolist::configure(Schema::make())
                            ->getComponents()
                    ),
                PrintAction::make('pdf.remision'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }
}
