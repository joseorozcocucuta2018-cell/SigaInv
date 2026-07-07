<?php

declare(strict_types=1);

namespace App\Filament\Resources\Cotizacions\Tables;

use App\Enums\CotizacionEstado;
use App\Filament\Actions\BulkResendEmailAction;
use App\Filament\Actions\PrintAction;
use App\Filament\Exports\CotizacionExporter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CotizacionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero')
                    ->label('Nro.')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('cliente.nombre')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                TextColumn::make('fecha')
                    ->label('Fecha')
                    ->dateTime('d/m/Y')
                    ->sortable(),
                TextColumn::make('total')
                    ->label('Total')
                    ->currency()
                    ->sortable(),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (CotizacionEstado $state): string => $state->label())
                    ->color(fn (CotizacionEstado $state): string => $state->color()),
            ])
            ->defaultSort('fecha', 'desc')
            ->recordActions([
                PrintAction::make('pdf.cotizacion'),
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()->visible(fn ($record) => $record->estado?->value === CotizacionEstado::PENDIENTE->value),
            ])
            ->toolbarActions([
                ExportAction::make()
                    ->label('Exportar')
                    ->exporter(CotizacionExporter::class),
                BulkActionGroup::make([
                    BulkResendEmailAction::make('cotizacion'),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
