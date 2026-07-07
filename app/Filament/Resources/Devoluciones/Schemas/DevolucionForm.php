<?php

declare(strict_types=1);

namespace App\Filament\Resources\Devoluciones\Schemas;

use App\Enums\DevolucionMotivo;
use App\Enums\DevolucionTipoDocumento;
use App\Enums\RemisionEstado;
use App\Enums\VentaEstado;
use App\Models\Producto;
use App\Models\Remision;
use App\Models\Venta;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class DevolucionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Documento a Devolver')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Select::make('tipo_documento')
                            ->label('Tipo de Documento')
                            ->options(DevolucionTipoDocumento::class)
                            ->required()
                            ->live()
                            ->disabled(fn ($operation) => $operation === 'edit'),
                        Select::make('documento_id')
                            ->label('Documento')
                            ->options(function (Get $get) {
                                $tipo = $get('tipo_documento');
                                if (! $tipo) {
                                    return [];
                                }

                                if ($tipo === DevolucionTipoDocumento::REMISION->value) {
                                    return Remision::where('estado', RemisionEstado::CONFIRMADA->value)
                                        ->where('fecha', '<=', now()->subDays(8))
                                        ->get()
                                        ->mapWithKeys(fn ($r) => [$r->id => "{$r->numero} - {$r->cliente?->nombre} ({$r->fecha->format('d/m/Y')})"])
                                        ->toArray();
                                } else {
                                    return Venta::where('estado', VentaEstado::CONFIRMADA->value)
                                        ->get()
                                        ->mapWithKeys(fn ($v) => [$v->id => "{$v->numero} - {$v->cliente?->nombre}"])
                                        ->toArray();
                                }
                            })
                            ->helperText(fn (Get $get) => $get('tipo_documento') === DevolucionTipoDocumento::REMISION->value
                                ? 'Solo remisiones con mas de 8 dias desde su emision'
                                : null)
                            ->searchable()
                            ->required()
                            ->disabled(fn ($operation) => $operation === 'edit'),

                        TextInput::make('numero')
                            ->label('Numero Devolucion')
                            ->disabled(),

                        Select::make('cliente_id')
                            ->label('Cliente')
                            ->relationship('cliente', 'nombre')
                            ->disabled()
                            ->preload(),
                    ]),

                Section::make('Datos de Devolucion')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Select::make('motivo')
                            ->label('Motivo')
                            ->options(DevolucionMotivo::class)
                            ->required(),

                        Textarea::make('observaciones')
                            ->label('Observaciones')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Productos Devueltos')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('detalles')
                            ->relationship('detalles')
                            ->label('Detalle de Devolucion')
                            ->columns(5)
                            ->schema([
                                Select::make('producto_id')
                                    ->label('Producto')
                                    ->options(Producto::where('activo', true)->pluck('nombre', 'id'))
                                    ->searchable()
                                    ->disabled()
                                    ->columnSpan(2),

                                TextInput::make('cantidad')
                                    ->label('Cantidad')
                                    ->numeric()
                                    ->required(),

                                TextInput::make('precio_unitario')
                                    ->label('Precio Unit.')
                                    ->numeric()
                                    ->disabled(),

                                TextInput::make('subtotal')
                                    ->label('Subtotal')
                                    ->numeric()
                                    ->disabled(),

                                Toggle::make('defectuoso')
                                    ->label('Defectuoso?')
                                    ->hint('Para garantia'),
                            ]),
                    ]),

                Section::make('Totales')
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->numeric()
                            ->disabled(),

                        TextInput::make('impuestos')
                            ->label('Impuestos')
                            ->numeric()
                            ->disabled(),

                        TextInput::make('total')
                            ->label('Total Devolucion')
                            ->numeric()
                            ->disabled(),
                    ]),
            ]);
    }
}
