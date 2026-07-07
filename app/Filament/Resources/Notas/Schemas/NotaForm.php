<?php

declare(strict_types=1);

namespace App\Filament\Resources\Notas\Schemas;

use App\Enums\NotaTipo;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Venta;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class NotaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos de la Nota')
                    ->columns(3)
                    ->schema([
                        Select::make('tipo')
                            ->options(NotaTipo::class)
                            ->required()
                            ->live(),
                        TextInput::make('numero')
                            ->label('Número')
                            ->required()
                            ->unique(ignoreRecord: true),
                        DatePicker::make('fecha')
                            ->default(now())
                            ->required(),
                        Select::make('cliente_id')
                            ->label('Cliente')
                            ->options(Cliente::pluck('nombre', 'id'))
                            ->searchable()
                            ->required()
                            ->live(),
                        Select::make('venta_id')
                            ->label('Venta Relacionada (Opcional)')
                            ->options(fn (Get $get) => Venta::where('cliente_id', $get('cliente_id'))->pluck('numero', 'id'))
                            ->searchable()
                            ->live(),
                        TextInput::make('motivo')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),
                    ]),

                Section::make('Detalle de Ítems')
                    ->schema([
                        Repeater::make('detalles')
                            ->relationship()
                            ->columns(5)
                            ->schema([
                                Select::make('producto_id')
                                    ->label('Producto')
                                    ->options(Producto::where('activo', true)->pluck('nombre', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->columnSpan(2)
                                    ->live()

                                    ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                        if ($state) {
                                            $producto = Producto::find($state);
                                            $set('precio_unitario', $producto->precio_venta);
                                        }
                                    }),
                                TextInput::make('cantidad')
                                    ->numeric()
                                    ->default(1)
                                    ->required()
                                    ->live()

                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::updateLineSubtotal($get, $set)),
                                TextInput::make('precio_unitario')
                                    ->numeric()
                                    ->prefix('$')
                                    ->required()
                                    ->live()

                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::updateLineSubtotal($get, $set)),
                                TextInput::make('subtotal')
                                    ->numeric()
                                    ->prefix('$')
                                    ->readOnly(),
                            ])
                            ->live()
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::updateTotals($get, $set)),
                    ]),

                Section::make('Totales')
                    ->columns(3)
                    ->schema([
                        TextInput::make('subtotal')
                            ->numeric()
                            ->prefix('$')
                            ->readOnly(),
                        TextInput::make('impuestos')
                            ->numeric()
                            ->prefix('$')
                            ->default(0),
                        TextInput::make('total')
                            ->numeric()
                            ->prefix('$')
                            ->readOnly(),
                    ]),
            ]);
    }

    public static function updateLineSubtotal(Get $get, Set $set): void
    {
        $cantidad = (float) $get('cantidad');
        $precio = (float) $get('precio_unitario');
        $set('subtotal', $cantidad * $precio);
    }

    public static function updateTotals(Get $get, Set $set): void
    {
        $detalles = $get('detalles') ?? [];
        $subtotal = collect($detalles)->sum('subtotal');
        $impuestos = (float) $get('impuestos');

        $set('subtotal', $subtotal);
        $set('total', $subtotal + $impuestos);
    }
}
