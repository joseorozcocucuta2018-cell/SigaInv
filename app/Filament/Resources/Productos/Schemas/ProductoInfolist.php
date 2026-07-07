<?php

declare(strict_types=1);

namespace App\Filament\Resources\Productos\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductoInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identificación')
                    ->description('Información básica del producto')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('codigo')
                            ->label('Código / SKU'),
                        TextEntry::make('codigo_barras')
                            ->label('Código de Barras')
                            ->placeholder('—'),
                        TextEntry::make('nombre')
                            ->label('Nombre'),
                        TextEntry::make('nombre_comun')
                            ->label('Nombre Común')
                            ->placeholder('—'),
                    ])->columnSpanFull(),

                Section::make('Clasificación')
                    ->description('Información de clasificación del producto')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('categoria.nombre')
                            ->label('Categoría')
                            ->placeholder('—'),
                        TextEntry::make('marca.nombre')
                            ->label('Marca')
                            ->placeholder('—'),
                        TextEntry::make('unidadMedida.nombre')
                            ->label('Unidad de Medida')
                            ->placeholder('—'),
                        TextEntry::make('impuesto.nombre')
                            ->label('Impuesto')
                            ->placeholder('—'),
                    ])->columnSpanFull(),

                Section::make('Precios')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('precio_compra')
                            ->label('Precio de Compra')
                            ->currency(),
                        TextEntry::make('costo_promedio')
                            ->label('Costo Promedio')
                            ->currency()
                            ->placeholder('—'),
                        TextEntry::make('precio_venta')
                            ->label('Precio de Venta')
                            ->currency(),
                    ])->columnSpanFull(),

                Section::make('Stock')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('stock_minimo')
                            ->label('Stock Mínimo'),
                        TextEntry::make('stock_maximo')
                            ->label('Stock Máximo'),
                        TextEntry::make('con_formula')
                            ->label('Producto de Fórmula')
                            ->badge()
                            ->color(fn ($state) => $state ? 'info' : 'gray')
                            ->formatStateUsing(fn ($state) => $state ? 'Sí' : 'No'),
                        TextEntry::make('exige_lote')
                            ->label('Controla por Lote')
                            ->badge()
                            ->color(fn ($state) => $state ? 'success' : 'gray')
                            ->formatStateUsing(fn ($state) => $state ? 'Sí' : 'No'),
                        TextEntry::make('exige_serial')
                            ->label('Controla por Serial')
                            ->badge()
                            ->color(fn ($state) => $state ? 'success' : 'gray')
                            ->formatStateUsing(fn ($state) => $state ? 'Sí' : 'No'),
                    ])->columnSpanFull(),

                Section::make('Imagen')
                    ->visible(fn ($record) => ! is_null($record->imagen))
                    ->schema([
                        ImageEntry::make('imagen')
                            ->label('')
                            ->disk('directo')
                            ->extraImgAttributes(['style' => 'height: 200px; object-fit: contain;'])
                            ->columnSpanFull(),
                    ]),

                Section::make('Precios por Proveedor')
                    ->description('Último precio de compra registrado por cada proveedor')
                    ->schema([
                        RepeatableEntry::make('historicoPrecios')
                            ->label('')
                            ->columns(2)
                            ->schema([
                                TextEntry::make('proveedor.nombre')
                                    ->label('Proveedor'),
                                TextEntry::make('precio_compra')
                                    ->label('Último Precio de Compra')
                                    ->currency(),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }
}
