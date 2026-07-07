<?php

declare(strict_types=1);

namespace App\Filament\Resources\Traslados\Schemas;

use App\Enums\BodegaEstado;
use App\Models\Bodega;
use App\Models\Producto;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TrasladoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos del Traslado')
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        Select::make('bodega_origen_id')
                            ->label('Bodega Origen')
                            ->options(Bodega::where('estado', BodegaEstado::ACTIVO)->pluck('nombre', 'id'))
                            ->searchable()
                            ->required()
                            ->helperText('Bodega de donde salen los productos'),
                        Select::make('bodega_destino_id')
                            ->label('Bodega Destino')
                            ->options(Bodega::where('estado', BodegaEstado::ACTIVO)->pluck('nombre', 'id'))
                            ->searchable()
                            ->required()
                            ->helperText('Bodega a donde llegan los productos'),
                        DateTimePicker::make('fecha')
                            ->label('Fecha')
                            ->default(now())
                            ->required(),
                    ]),

                Section::make('Productos a Trasladar')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('detalles')
                            ->label('Productos')
                            ->relationship()
                            ->columns(4)
                            ->minItems(1)
                            ->schema([
                                Select::make('producto_id')
                                    ->label('Producto')
                                    ->options(
                                        Producto::where('activo', true)
                                            ->pluck('nombre', 'id')
                                            ->mapWithKeys(fn ($nombre, $id) => [
                                                $id => strlen($nombre) > 40 ? mb_substr($nombre, 0, 40).'…' : $nombre,
                                            ])
                                    )
                                    ->searchable()
                                    ->required(),
                                TextInput::make('cantidad')
                                    ->label('Cantidad')
                                    ->numeric()
                                    ->default(1)
                                    ->required()
                                    ->minValue(0.001),
                                TextInput::make('lote')
                                    ->label('Lote')
                                    ->maxLength(50),
                                DatePicker::make('fecha_vencimiento')
                                    ->label('Vencimiento'),
                            ])
                            ->defaultItems(1)
                            ->addActionLabel('Agregar producto')
                            ->columnSpanFull(),
                    ]),

                Section::make('Observaciones')
                    ->columnSpanFull()
                    ->schema([
                        Textarea::make('observaciones')
                            ->label('Observaciones')
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
