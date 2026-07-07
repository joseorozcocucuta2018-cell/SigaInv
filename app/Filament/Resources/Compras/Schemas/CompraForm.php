<?php

declare(strict_types=1);

namespace App\Filament\Resources\Compras\Schemas;

use App\Enums\BodegaEstado;
use App\Enums\ProveedorEstado;
use App\Filament\Forms\ProveedorQuickCreate;
use App\Filament\Resources\Productos\Schemas\ProductoForm;
use App\Filament\Schemas\DocumentTotalsHelper;
use App\Models\Bodega;
use App\Models\Empresa;
use App\Models\Impuesto;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Services\BodegaService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class CompraForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos de la Compra')
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        TextInput::make('numero')
                            ->label('Número de Factura Compra')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(20)
                            ->disabled(fn ($operation) => $operation === 'edit'),
                        Select::make('proveedor_id')
                            ->label('Proveedor')
                            ->options(Proveedor::where('estado', ProveedorEstado::ACTIVO)->pluck('nombre', 'id'))
                            ->searchable()
                            ->required()
                            ->live()
                            ->createOptionModalHeading('Nuevo Proveedor')
                            ->createOptionForm(ProveedorQuickCreate::form())
                            ->createOptionUsing(ProveedorQuickCreate::using()),
                        Select::make('bodega_id')
                            ->label('Bodega de Entrada')
                            ->options(Bodega::where('estado', BodegaEstado::ACTIVO)->pluck('nombre', 'id'))
                            ->default(fn () => BodegaService::bodegaDefaultId())
                            ->searchable()
                            ->required()
                            ->disabled(BodegaService::bodegaDeshabilitada())
                            ->dehydrated(true),
                        DateTimePicker::make('fecha')
                            ->label('Fecha')
                            ->default(now())
                            ->required(),
                        DatePicker::make('fecha_vencimiento')
                            ->label('Fecha Vencimiento'),
                    ]),

                Section::make('Detalle de Productos')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('detalles')
                            ->relationship()
                            ->columns(4)
                            ->minItems(1, 'Debe agregar al menos un detalle a la compra')
                            ->schema([
                                Select::make('producto_id')
                                    ->label('Producto')
                                    ->options(
                                        Producto::where('activo', true)
                                            ->whereIn('tipo_producto', ['comprado', 'materia_prima'])
                                            ->pluck('nombre', 'id')
                                            ->mapWithKeys(fn ($nombre, $id) => [
                                                $id => strlen($nombre) > 40 ? mb_substr($nombre, 0, 40).'…' : $nombre,
                                            ])
                                    )
                                    ->searchable()
                                    ->required()
                                    ->distinct()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->columnSpan(2)
                                    ->createOptionForm(ProductoForm::getQuickCreateComponents())
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                                        if (! $state) {
                                            return;
                                        }
                                        $producto = Producto::find($state);
                                        if ($producto) {
                                            // En compras tomamos el precio_compra del producto
                                            $set('precio_unitario', $producto->precio_compra);
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

                                            if ((float) $value !== 1.0) {
                                                $fail('Los productos controlados por serial deben ingresarse con cantidad 1 por línea.');
                                            }
                                        };
                                    }),
                                TextInput::make('precio_unitario')
                                    ->label('Precio Unit.')
                                    ->numeric()
                                    ->prefix('$')
                                    ->required()
                                    ->columnSpan(1)
                                    ->minValue(0, 'El precio unitario no puede ser negativo')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Set $set, Get $get) {
                                        static::handlePriceCalculation($get, $set);
                                        DocumentTotalsHelper::updateLineSubtotal($get, $set);
                                        DocumentTotalsHelper::updateTotals($get, $set);
                                    }),
                                TextInput::make('lote')
                                    ->label('Lote')
                                    ->columnSpan(1)
                                    ->disabled(function (Get $get): bool {
                                        if (! $get('producto_id')) {
                                            return true;
                                        }
                                        $producto = Producto::find($get('producto_id'));

                                        return ! ($producto && $producto->exige_lote);
                                    })
                                    ->live()
                                    ->rule(function (Get $get) {
                                        return function (string $attribute, $value, \Closure $fail) use ($get) {
                                            if (! $get('producto_id')) {
                                                return;
                                            }
                                            $producto = Producto::find($get('producto_id'));
                                            if (! $producto || ! $producto->exige_lote) {
                                                return;
                                            }

                                            if (! $value) {
                                                $fail('Debe ingresar el lote para este producto.');
                                            }
                                        };
                                    }),
                                TextInput::make('serial')
                                    ->label('Serial')
                                    ->columnSpan(1)
                                    ->disabled(function (Get $get): bool {
                                        if (! $get('producto_id')) {
                                            return true;
                                        }
                                        $producto = Producto::find($get('producto_id'));
                                        $empresa = Empresa::first();

                                        return ! ($producto && $producto->exige_serial && $empresa && $empresa->usa_seriales);
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
                                                $fail('Debe ingresar un número de serie para este producto.');
                                            }
                                        };
                                    }),
                                DatePicker::make('fecha_vencimiento')
                                    ->label('Vencimiento')
                                    ->columnSpan(1)
                                    ->disabled(function (Get $get): bool {
                                        if (! $get('producto_id')) {
                                            return true;
                                        }
                                        $producto = Producto::find($get('producto_id'));

                                        return ! ($producto && $producto->exige_lote);
                                    })
                                    ->live()
                                    ->rule(function (Get $get) {
                                        return function (string $attribute, $value, \Closure $fail) use ($get) {
                                            if (! $get('producto_id')) {
                                                return;
                                            }
                                            $producto = Producto::find($get('producto_id'));
                                            if (! $producto || ! $producto->exige_lote) {
                                                return;
                                            }

                                            if (! $value) {
                                                $fail('Debe ingresar la fecha de vencimiento para este producto.');
                                            }
                                        };
                                    }),
                                Select::make('impuesto_id')
                                    ->label('Impuesto')
                                    ->options(Impuesto::where('activo', true)->pluck('nombre', 'id'))
                                    ->placeholder('Ninguno')
                                    ->columnSpan(1)
                                    ->live()
                                    ->disabled(! Empresa::isResponsableIva())
                                    ->default(function () {
                                        if (! Empresa::isResponsableIva()) {
                                            return DocumentTotalsHelper::ivaCero()?->id;
                                        }

                                        return null;
                                    })
                                    ->afterStateUpdated(function (Set $set, Get $get) {
                                        static::handlePriceCalculation($get, $set);
                                        DocumentTotalsHelper::updateTotals($get, $set);
                                    }),
                                Toggle::make('iva_incluido')
                                    ->label('¿IVA Inc.?')
                                    ->default(false)
                                    ->columnSpan(1)
                                    ->live()
                                    ->afterStateUpdated(fn (Set $set, Get $get) => static::handlePriceCalculation($get, $set)),
                                TextInput::make('descuento_unitario')
                                    ->label('Desc. Unit.')
                                    ->numeric()
                                    ->prefix('$')
                                    ->default(0)
                                    ->columnSpan(1)
                                    ->minValue(0, 'El descuento no puede ser negativo')
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, Get $get) {
                                        DocumentTotalsHelper::updateLineSubtotal($get, $set);
                                        DocumentTotalsHelper::updateTotals($get, $set);
                                    }),
                                TextInput::make('subtotal')
                                    ->label('Subtotal')
                                    ->numeric()
                                    ->prefix('$')
                                    ->default(0)
                                    ->columnSpan(2)
                                    ->readOnly(),
                            ])
                            ->defaultItems(1)
                            ->addActionLabel('Agregar ítem')
                            ->columnSpanFull(),
                    ]),

                DocumentTotalsHelper::totalesSectionConSaldo(),

                Textarea::make('observaciones')
                    ->label('Observaciones')
                    ->columnSpanFull(),
            ]);
    }

    /**
     * Ajuste de precio cuando el usuario marca "IVA incluido" en la línea.
     * Divide el precio entre (1 + porcentaje) para obtener el precio neto.
     * Único de Compra — Venta/Remision/Cotizacion no tienen este toggle.
     */
    public static function handlePriceCalculation(Get $get, Set $set): void
    {
        if (! $get('iva_incluido')) {
            return;
        }

        $precioEntrada = (float) ($get('precio_unitario') ?? 0);
        $impuestoId = $get('impuesto_id');

        if (! $impuestoId || $precioEntrada <= 0) {
            return;
        }

        $impuesto = Impuesto::find($impuestoId);
        $porcentaje = $impuesto ? (float) $impuesto->porcentaje : 0;

        if ($porcentaje > 0) {
            $precioNeto = $precioEntrada / (1 + ($porcentaje / 100));
            $set('precio_unitario', round($precioNeto, 4));
            $set('iva_incluido', false);
        }
    }
}
