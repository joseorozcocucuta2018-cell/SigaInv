<?php

declare(strict_types=1);

namespace App\Filament\Resources\Remisions\Schemas;

use App\Enums\BodegaEstado;
use App\Enums\ClienteEstado;
use App\Enums\CotizacionEstado;
use App\Enums\EstadoPagoEnum;
use App\Filament\Forms\ClienteQuickCreate;
use App\Filament\Schemas\DocumentTotalsHelper;
use App\Models\Bodega;
use App\Models\Cliente;
use App\Models\Cotizacion;
use App\Models\Empresa;
use App\Models\Impuesto;
use App\Models\Producto;
use App\Observers\CotizacionObserver;
use App\Services\BodegaService;
use App\Services\PrecioService;
use App\Services\StockService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class RemisionForm
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
                            ->unique(ignoreRecord: true)
                            ->maxLength(20)
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('El número se asigna automáticamente al guardar.'),
                        Select::make('cliente_id')
                            ->label('Cliente')
                            ->options(Cliente::where('estado', ClienteEstado::ACTIVO)->pluck('nombre', 'id'))
                            ->searchable()
                            ->required()
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
                        Select::make('cotizacion_id')
                            ->label('Cotización')
                            ->options(function () {
                                return Cotizacion::whereIn('estado', [
                                    CotizacionEstado::PENDIENTE->value,
                                    CotizacionEstado::ENVIADA->value,
                                ])
                                    ->where(function ($query) {
                                        $query->whereNull('fecha_vigencia')
                                            ->orWhere('fecha_vigencia', '>=', now()->toDateString());
                                    })
                                    ->pluck('numero', 'id');
                            })
                            ->searchable()
                            ->placeholder('Ninguna')
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                                if (! $state) {
                                    return;
                                }
                                $cotizacion = Cotizacion::with('detalles')->find($state);
                                if (! $cotizacion) {
                                    return;
                                }

                                try {
                                    CotizacionObserver::validateForUse($cotizacion);
                                } catch (\InvalidArgumentException $e) {
                                    Notification::make()
                                        ->danger()
                                        ->title('Cotización No Válida')
                                        ->body($e->getMessage())
                                        ->send();
                                    $set('cotizacion_id', null);

                                    return;
                                }

                                $detalles = $cotizacion->detalles->map(function ($detalle) {
                                    return [
                                        'producto_id' => $detalle->producto_id,
                                        'cantidad' => $detalle->cantidad,
                                        'precio_unitario' => $detalle->precio_unitario,
                                        'descuento_unitario' => $detalle->descuento_unitario,
                                        'impuesto_id' => $detalle->impuesto_id,
                                        'subtotal' => $detalle->subtotal,
                                    ];
                                })->toArray();

                                $set('detalles', $detalles);
                                $set('cliente_id', $cotizacion->cliente_id);
                                DocumentTotalsHelper::updateTotals($get, $set);
                            }),
                        DatePicker::make('fecha_vencimiento')
                            ->label('Fecha Vencimiento'),
                        Select::make('estado_pago')
                            ->label('Estado de Pago')
                            ->options(EstadoPagoEnum::class)
                            ->default(EstadoPagoEnum::PENDIENTE->value)
                            ->required(),
                    ]),

                Section::make('Detalle de Productos')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('detalles')
                            ->relationship()
                            ->columns(7)
                            ->minItems(1, 'Debe agregar al menos un detalle a la remisión')
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
                                            DocumentTotalsHelper::updateTotals($get, $set);
                                        }
                                    }),
                                TextInput::make('lote')
                                    ->label('Lote')
                                    ->columnSpan(1)
                                    ->live(),
                                TextInput::make('serial')
                                    ->label('Serial')
                                    ->columnSpan(1)
                                    ->visible(function (Get $get): bool {
                                        if (! $get('producto_id')) {
                                            return false;
                                        }
                                        $producto = Producto::find($get('producto_id'));
                                        $empresa = Empresa::first();

                                        return $producto && $producto->exige_serial && $empresa && $empresa->usa_seriales;
                                    })
                                    ->rule(function (Get $get) {
                                        return function (string $attribute, $value, \Closure $fail) use ($get) {
                                            if (! $get('producto_id')) {
                                                return;
                                            }
                                            $producto = Producto::find($get('producto_id'));
                                            $empresa = Empresa::first();
                                            if (! $producto || ! $producto->exige_serial || ! $empresa || ! $empresa->usa_seriales) {
                                                return;
                                            }

                                            if (! $value) {
                                                $fail('Debe seleccionar o ingresar un número de serie para este producto.');
                                            }
                                        };
                                    }),
                                DatePicker::make('fecha_vencimiento')
                                    ->label('Vencimiento')
                                    ->columnSpan(1)
                                    ->live(),
                                TextInput::make('cantidad')
                                    ->label('Cantidad')
                                    ->numeric()
                                    ->default(1)
                                    ->required()
                                    ->minValue(0.001, 'La cantidad debe ser mayor a 0')
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, Get $get) {
                                        DocumentTotalsHelper::updateLineSubtotal($get, $set);
                                        DocumentTotalsHelper::updateTotals($get, $set);
                                    })
                                    ->rules([
                                        fn (Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                            $bodegaId = $get('../../bodega_id');
                                            $productoId = $get('producto_id');
                                            if (! $bodegaId || ! $productoId) {
                                                return;
                                            }

                                            $producto = Producto::find($productoId);
                                            $empresa = Empresa::first();

                                            if ($producto && $producto->exige_serial && $empresa && $empresa->usa_seriales) {
                                                if ((float) $value !== 1.0) {
                                                    $fail('Los productos controlados por serial deben remitirse con cantidad 1 por línea.');

                                                    return;
                                                }
                                            }

                                            $lote = $get('lote');
                                            $vence = $get('fecha_vencimiento');
                                            $actual = StockService::getAvailableStock($productoId, $bodegaId, $lote, $vence);

                                            if ($value > $actual) {
                                                $fail("Stock insuficiente en la bodega seleccionada. Disponible: {$actual}");
                                            }
                                        },
                                    ]),
                                TextInput::make('precio_unitario')
                                    ->label('Precio Unit.')
                                    ->numeric()
                                    ->prefix('$')
                                    ->required()
                                    ->minValue(0, 'El precio unitario no puede ser negativo')
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, Get $get) {
                                        static::updateLineSubtotal($get, $set);
                                        static::updateTotals($get, $set);
                                    }),
                                TextInput::make('descuento_unitario')
                                    ->label('Desc. Unit.')
                                    ->numeric()
                                    ->prefix('$')
                                    ->default(0)
                                    ->minValue(0, 'El descuento no puede ser negativo')
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, Get $get) {
                                        static::updateLineSubtotal($get, $set);
                                        static::updateTotals($get, $set);
                                    }),
                                TextInput::make('subtotal')
                                    ->label('Subtotal')
                                    ->numeric()
                                    ->prefix('$')
                                    ->default(0)
                                    ->readOnly(),
                                Select::make('impuesto_id')
                                    ->label('Impuesto')
                                    ->options(Impuesto::where('activo', true)->pluck('nombre', 'id'))
                                    ->placeholder('Ninguno')
                                    ->live()
                                    ->disabled(! Empresa::isResponsableIva())
                                    ->default(function () {
                                        if (! Empresa::isResponsableIva()) {
                                            return DocumentTotalsHelper::ivaCero()?->id;
                                        }

                                        return null;
                                    })
                                    ->afterStateUpdated(fn (Set $set, Get $get) => DocumentTotalsHelper::updateTotals($get, $set)),
                            ])
                            ->defaultItems(1)
                            ->addActionLabel('Agregar producto')
                            ->columnSpanFull(),
                    ]),

                DocumentTotalsHelper::totalesSectionConSaldo(),

                Textarea::make('observaciones')
                    ->label('Observaciones')
                    ->columnSpanFull(),
            ]);
    }
}
