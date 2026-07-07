<?php

declare(strict_types=1);

namespace App\Filament\Resources\Cotizacions\Schemas;

use App\Enums\BodegaEstado;
use App\Enums\ClienteEstado;
use App\Enums\CotizacionEstado;
use App\Filament\Forms\ClienteQuickCreate;
use App\Filament\Schemas\DocumentTotalsHelper;
use App\Models\Bodega;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Producto;
use App\Services\BodegaService;
use App\Services\CotizacionService;
use App\Services\PrecioService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class CotizacionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos Generales')
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        TextInput::make('numero')
                            ->label('Número')
                            ->placeholder('Se genera automáticamente')
                            ->maxLength(20)
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('El número se asigna automáticamente al guardar.'),
                        Select::make('cliente_id')
                            ->label('Cliente')
                            ->options(
                                Cliente::where('estado', ClienteEstado::ACTIVO)
                                    ->where('id', '!=', 1) // excluir CLIENTES VARIOS
                                    ->orderBy('nombre')
                                    ->pluck('nombre', 'id')
                            )
                            ->searchable()
                            ->required()
                            ->live()
                            ->createOptionForm(ClienteQuickCreate::form())
                            ->createOptionUsing(ClienteQuickCreate::using()),
                        Select::make('bodega_id')
                            ->label('Bodega')
                            ->options(Bodega::where('estado', BodegaEstado::ACTIVO)->pluck('nombre', 'id'))
                            ->searchable()
                            ->required()
                            ->default(fn () => BodegaService::bodegaDefaultId())
                            ->disabled(BodegaService::bodegaDeshabilitada())
                            ->dehydrated(true),
                        DateTimePicker::make('fecha')
                            ->label('Fecha')
                            ->default(now()),
                        DatePicker::make('fecha_vigencia')
                            ->label('Fecha de Vigencia')
                            ->default(now()->addDays(8)),
                        Select::make('estado')
                            ->label('Estado')
                            ->options(function ($record) {
                                if (! $record) {
                                    return [CotizacionEstado::PENDIENTE->value => CotizacionEstado::PENDIENTE->label()];
                                }
                                $transiciones = CotizacionService::TRANSICIONES[$record->estado->value] ?? [];
                                $opciones = [$record->estado->value => $record->estado->label()];
                                foreach ($transiciones as $t) {
                                    $estado = CotizacionEstado::from($t);
                                    $opciones[$estado->value] = $estado->label();
                                }

                                return $opciones;
                            })
                            ->default(CotizacionEstado::PENDIENTE->value)
                            ->required()
                            ->hiddenOn('create')
                            ->disabled(fn ($record) => $record && in_array($record->estado?->value, [CotizacionEstado::RECHAZADA->value, CotizacionEstado::VENCIDA->value, CotizacionEstado::ACEPTADA->value])),
                    ]),

                Section::make('Detalle de Productos')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('detalles')
                            ->relationship()
                            ->columns(6)
                            ->schema([
                                Select::make('producto_id')
                                    ->label('Producto')
                                    ->options(
                                        Producto::where('activo', true)
                                            ->pluck('nombre', 'id')
                                            ->mapWithKeys(fn ($nombre, $id) => [
                                                $id => strlen($nombre) > 45 ? mb_substr($nombre, 0, 45).'…' : $nombre,
                                            ])
                                    )
                                    ->searchable()
                                    ->required()
                                    ->columnSpan(2)
                                    ->live()

                                    ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                                        if (! $state) {
                                            return;
                                        }
                                        $producto = Producto::find($state);
                                        if ($producto) {
                                            $clienteId = $get('../../cliente_id');
                                            $precio = app(PrecioService::class)->calcularPrecioConDescuento($producto, $clienteId ? Cliente::find($clienteId) : null);
                                            $set('precio_unitario', $precio);
                                            if (Empresa::isResponsableIva()) {
                                                $set('impuesto_id', $producto->impuesto_id);
                                            } else {
                                                $ivaCero = DocumentTotalsHelper::ivaCero();
                                                $set('impuesto_id', $ivaCero?->id);
                                            }
                                            DocumentTotalsHelper::updateLineSubtotal($get, $set);
                                            DocumentTotalsHelper::updateTotals($get, $set, includeSaldo: false);
                                        }
                                    }),
                                TextInput::make('cantidad')
                                    ->label('Cantidad')
                                    ->numeric()
                                    ->default(1)
                                    ->required()
                                    ->live()

                                    ->afterStateUpdated(function (Set $set, Get $get) {
                                        DocumentTotalsHelper::updateLineSubtotal($get, $set);
                                        DocumentTotalsHelper::updateTotals($get, $set, includeSaldo: false);
                                    }),
                                TextInput::make('precio_unitario')
                                    ->label('Precio Unit.')
                                    ->numeric()
                                    ->prefix('$')
                                    ->required()
                                    ->live()

                                    ->afterStateUpdated(function (Set $set, Get $get) {
                                        DocumentTotalsHelper::updateLineSubtotal($get, $set);
                                        DocumentTotalsHelper::updateTotals($get, $set, includeSaldo: false);
                                    }),
                                TextInput::make('descuento_unitario')
                                    ->label('Desc. Unit.')
                                    ->numeric()
                                    ->prefix('$')
                                    ->default(0)
                                    ->live()

                                    ->afterStateUpdated(function (Set $set, Get $get) {
                                        DocumentTotalsHelper::updateLineSubtotal($get, $set);
                                        DocumentTotalsHelper::updateTotals($get, $set, includeSaldo: false);
                                    }),
                                TextInput::make('subtotal')
                                    ->label('Subtotal')
                                    ->numeric()
                                    ->prefix('$')
                                    ->default(0)
                                    ->readOnly(),
                                // IVA se asigna automáticamente al seleccionar el producto,
                                // no se muestra por línea — se acumula en el total general
                                Select::make('impuesto_id')
                                    ->hidden()
                                    ->live()
                                    ->disabled(! Empresa::isResponsableIva())
                                    ->default(function () {
                                        if (! Empresa::isResponsableIva()) {
                                            return DocumentTotalsHelper::ivaCero()?->id;
                                        }

                                        return null;
                                    })
                                    ->afterStateUpdated(fn (Set $set, Get $get) => DocumentTotalsHelper::updateTotals($get, $set, includeSaldo: false)),
                            ])
                            ->defaultItems(1)
                            ->addActionLabel('Agregar producto')
                            ->columnSpanFull(),
                    ]),

                DocumentTotalsHelper::totalesSectionSinSaldo(),

                Textarea::make('observaciones')
                    ->label('Observaciones')
                    ->columnSpanFull(),
            ]);
    }
}
