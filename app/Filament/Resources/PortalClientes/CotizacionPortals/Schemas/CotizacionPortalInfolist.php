<?php

declare(strict_types=1);

namespace App\Filament\Resources\PortalClientes\CotizacionPortals\Schemas;

use App\Enums\CotizacionEstado;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CotizacionPortalInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información de la Cotización')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('numero')
                            ->label('Número'),

                        TextEntry::make('fecha')
                            ->label('Fecha')
                            ->date('d/m/Y'),

                        TextEntry::make('fecha_vigencia')
                            ->label('Vigencia')
                            ->date('d/m/Y')
                            ->placeholder('—'),

                        TextEntry::make('estado')
                            ->label('Estado')
                            ->badge()
                            ->color(fn (CotizacionEstado $state): string => $state->color())
                            ->formatStateUsing(fn (CotizacionEstado $state): string => $state->label()),
                    ]),

                Section::make('Valores')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('subtotal')
                            ->label('Subtotal')
                            ->currency()
                            ->placeholder('—'),

                        TextEntry::make('impuestos')
                            ->label('Impuestos')
                            ->currency()
                            ->placeholder('—'),

                        TextEntry::make('total')
                            ->label('Total')
                            ->currency()
                            ->weight('bold'),
                    ]),

                Section::make('Productos')
                    ->schema([
                        RepeatableEntry::make('detalles')
                            ->label('')
                            ->schema([
                                TextEntry::make('producto.nombre')
                                    ->label('Producto')
                                    ->columnSpan(2),

                                TextEntry::make('cantidad')
                                    ->label('Cantidad')
                                    ->numeric(decimalPlaces: 2),

                                TextEntry::make('precio_unitario')
                                    ->label('Precio Unitario')
                                    ->currency(),

                                TextEntry::make('subtotal')
                                    ->label('Subtotal')
                                    ->currency()
                                    ->weight('bold'),
                            ])
                            ->columns(6)
                            ->placeholder('Sin productos'),
                    ]),

                Section::make('Observaciones')
                    ->visible(fn ($record): bool => ! empty($record->observaciones))
                    ->schema([
                        TextEntry::make('observaciones')
                            ->label('')
                            ->columnSpanFull()
                            ->placeholder('—'),
                    ]),
            ]);
    }
}
