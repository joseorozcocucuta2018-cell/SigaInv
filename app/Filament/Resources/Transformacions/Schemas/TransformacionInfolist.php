<?php

declare(strict_types=1);

namespace App\Filament\Resources\Transformacions\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;

class TransformacionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos Generales')
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        TextEntry::make('productoFinal.nombre')
                            ->label('Producto Final')
                            ->size(TextSize::Large)
                            ->weight(FontWeight::Bold)
                            ->columnSpan(2),
                        TextEntry::make('cantidad_a_producir')
                            ->label('Cantidad Producida')
                            ->numeric(3)
                            ->size(TextSize::Large),

                        TextEntry::make('bodega.nombre')
                            ->label('Bodega'),
                        TextEntry::make('tipo')
                            ->label('Tipo')
                            ->badge()
                            ->formatStateUsing(fn ($state) => $state?->label() ?? $state)
                            ->color(fn ($state): string => $state?->color() ?? 'gray'),
                        TextEntry::make('estado')
                            ->label('Estado')
                            ->badge()
                            ->formatStateUsing(fn ($state) => $state->label())
                            ->color(fn ($state): string => $state->color()),

                        TextEntry::make('fecha')
                            ->label('Fecha')
                            ->dateTime('d/m/Y H:i'),
                        TextEntry::make('confirmada_en')
                            ->label('Confirmada el')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('—'),
                        TextEntry::make('revertida_en')
                            ->label('Revertida el')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('—'),

                        TextEntry::make('formula.producto_final_nombre')
                            ->label('Fórmula Aplicada')
                            ->placeholder('Sin fórmula vinculada')
                            ->columnSpan(3),
                    ]),

                Section::make('Precio y Costo')
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        TextEntry::make('tipo_calculo_precio')
                            ->label('Cálculo del precio')
                            ->formatStateUsing(fn ($state) => $state?->label() ?? $state)
                            ->badge()
                            ->color('info'),
                        TextEntry::make('costo_total')
                            ->label('Costo total')
                            ->money('COP')
                            ->placeholder('—'),
                        TextEntry::make('margen_deseado')
                            ->label('Margen')
                            ->suffix('%')
                            ->placeholder('—'),
                        TextEntry::make('precio_sugerido')
                            ->label('Precio sugerido')
                            ->money('COP')
                            ->placeholder('—')
                            ->columnSpan(3),
                    ]),

                Section::make('Insumos Consumidos')
                    ->columnSpanFull()
                    ->schema([
                        RepeatableEntry::make('insumos')
                            ->label('')
                            ->columns(3)
                            ->contained(false)
                            ->schema([
                                TextEntry::make('producto.nombre')
                                    ->label('Insumo')
                                    ->columnSpan(1),
                                TextEntry::make('cantidad')
                                    ->label('Cantidad')
                                    ->numeric(3),
                                TextEntry::make('lote')
                                    ->label('Lote')
                                    ->placeholder('—'),
                            ]),
                    ]),

                Section::make('Observaciones')
                    ->schema([
                        TextEntry::make('observaciones')
                            ->label('')
                            ->placeholder('Sin observaciones')
                            ->columnSpanFull(),
                    ])
                    ->collapsed()
                    ->collapsible(),
            ]);
    }
}
