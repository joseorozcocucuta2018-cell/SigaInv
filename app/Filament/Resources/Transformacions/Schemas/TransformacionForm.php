<?php

declare(strict_types=1);

namespace App\Filament\Resources\Transformacions\Schemas;

use App\Enums\BodegaEstado;
use App\Enums\TipoCalculoPrecio;
use App\Enums\TransformacionEstado;
use App\Enums\TransformacionLineaTipo;
use App\Enums\TransformacionTipo;
use App\Models\Bodega;
use App\Models\Categoria;
use App\Models\Empresa;
use App\Models\FormulaTransformacion;
use App\Models\Impuesto;
use App\Models\Marca;
use App\Models\Producto;
use App\Models\StockBodega;
use App\Models\Transformacion;
use Filament\Forms\Components\Select;
use App\Models\UnidadMedida;
use App\Services\BodegaService;
use App\Services\CostoService;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class TransformacionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos Generales')
                    ->columns(2)
                    ->schema([
                        Select::make('bodega_id')
                            ->label('Bodega')
                            ->options(Bodega::where('estado', BodegaEstado::ACTIVO)->pluck('nombre', 'id'))
                            ->searchable()
                            ->required()
                            ->default(fn () => BodegaService::bodegaDefaultId())
                            ->disabled(BodegaService::bodegaDeshabilitada())
                            ->dehydrated(true),
                        Select::make('tipo')
                            ->label('Tipo de Transformacion')
                            ->options(TransformacionTipo::class)
                            ->default(TransformacionTipo::FABRICACION->value)
                            ->required()
                            ->live(),
                        Select::make('estado')
                            ->label('Estado')
                            ->options(TransformacionEstado::class)
                            ->default(TransformacionEstado::BORRADOR->value)
                            ->disabled()
                            ->dehydrated(),
                        DateTimePicker::make('fecha')
                            ->label('Fecha')
                            ->default(now()),
                    ])->columnSpanFull(),

                Section::make('Producto a Generar')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Select::make('producto_final_id')
                            ->label('Producto Final')
                            ->options(function (Get $get) {
                                $tipo = $get('tipo');

                                return Producto::where('con_formula', true)
                                    ->where('tipo_producto', 'manufacturado')
                                    ->whereHas('formula', fn ($q) => $q->where('tipo', $tipo)->where('activo', true))
                                    ->pluck('nombre', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->createOptionForm(fn (Select $component): array => self::quickCreateProductoForm(
                                'manufacturado',
                                $component->getLivewire()->data['tipo'] ?? null,
                            ))
                            ->createOptionUsing(function (array $data, Select $component): int {
                                $tipo = $component->getLivewire()->data['tipo'] ?? null;

                                if ($tipo) {
                                    $enumTipo = TransformacionTipo::tryFrom($tipo);
                                    if ($enumTipo) {
                                        if (empty($data['categoria_id'])) {
                                            $categoria = $enumTipo->categoriaSugerida()
                                                ?? Categoria::create([
                                                    'nombre' => $enumTipo->label(),
                                                    'activo' => true,
                                                ]);
                                            $data['categoria_id'] = $categoria->id;
                                        }

                                        if (empty($data['marca_id'])) {
                                            $marca = $enumTipo->marcaSugerida()
                                                ?? Marca::create([
                                                    'nombre' => $enumTipo->label(),
                                                    'activo' => true,
                                                ]);
                                            $data['marca_id'] = $marca->id;
                                        }
                                    }
                                }

                                if (empty($data['unidad_medida_id'])) {
                                    $data['unidad_medida_id'] = 1;
                                }

                                if (empty($data['impuesto_id'])) {
                                    $data['impuesto_id'] = Empresa::isResponsableIva()
                                        ? Impuesto::where('tipo', 'IVA')->where('porcentaje', 19)->where('activo', true)->value('id')
                                        : Impuesto::where('tipo', 'IVA')->where('porcentaje', 0)->where('activo', true)->value('id');
                                }

                                return Producto::create([
                                    ...$data,
                                    'con_formula' => true,
                                ])->id;
                            })
                            ->helperText(fn (Get $get): string => match ($get('tipo')) {
                                TransformacionTipo::FABRICACION->value => 'Producto manufacturado que resultara de esta transformacion',
                                TransformacionTipo::REENVASE->value => 'Producto en la nueva presentacion o empaque',
                                TransformacionTipo::COMBO->value => 'Producto bundle que agrupa los componentes',
                                TransformacionTipo::PROMO->value => 'Producto promocional que se generara',
                                default => 'Seleccione el producto que resultara de la transformacion',
                            })
                            ->afterStateUpdated(function (Set $set, ?int $state) {
                                if (! $state) {
                                    $set('formula_transformacion_id', null);
                                    $set('detalles', []);
                                    $set('_producto_tiene_stock', false);

                                    return;
                                }

                                $tieneStock = StockBodega::where('producto_id', $state)
                                    ->where('cantidad', '>', 0)
                                    ->exists();
                                $set('_producto_tiene_stock', $tieneStock);

                                if ($tieneStock) {
                                    $ultima = Transformacion::where('producto_final_id', $state)
                                        ->where('estado', TransformacionEstado::CONFIRMADA)
                                        ->orderBy('id', 'desc')
                                        ->first();

                                    if ($ultima) {
                                        $set('tipo_calculo_precio', $ultima->tipo_calculo_precio?->value);
                                        $set('margen_deseado', $ultima->margen_deseado);
                                        $set('precio_sugerido', $ultima->precio_sugerido);
                                    }
                                } else {
                                    $formulaTipo = FormulaTransformacion::where('producto_final_id', $state)
                                        ->where('activo', true)
                                        ->first()?->tipo;
                                    $set('tipo_calculo_precio', TipoCalculoPrecio::defaultForTipo(
                                        $formulaTipo ?? TransformacionTipo::FABRICACION
                                    )->value);
                                }

                                $formula = FormulaTransformacion::with('detalles')->where('producto_final_id', $state)
                                    ->where('activo', true)
                                    ->first();

                                if ($formula) {
                                    $set('formula_transformacion_id', $formula->id);
                                    $set('tipo', $formula->tipo);

                                    $detalles = $formula->detalles->map(function ($detalle) {
                                        $producto = $detalle->producto;

                                        return [
                                            'tipo_linea' => 'insumo',
                                            'producto_id' => $detalle->producto_id,
                                            'cantidad' => $detalle->cantidad,
                                            'costo_unitario' => CostoService::resolveCostoUnitario($producto),
                                        ];
                                    })->toArray();

                                    $set('detalles', $detalles);
                                } else {
                                    $set('formula_transformacion_id', null);
                                    $set('detalles', []);
                                }
                            })
                            ->columnSpan(2),

                        TextInput::make('cantidad_a_producir')
                            ->label('Cantidad a Producir')
                            ->numeric()
                            ->minValue(0.001)
                            ->default(1)
                            ->required()
                            ->helperText('Cuántas unidades del producto final se van a generar'),

                        Select::make('formula_transformacion_id')
                            ->label('Fórmula Detectada')
                            ->relationship('formula', 'producto_final_nombre')
                            ->disabled()
                            ->dehydrated()
                            ->nullable()
                            ->live()
                            ->afterStateUpdated(function (Set $set, ?int $state) {
                                if (! $state) {
                                    $set('detalles', []);

                                    return;
                                }

                                $formula = FormulaTransformacion::with('detalles')->find($state);
                                if ($formula) {
                                    $detalles = $formula->detalles->map(function ($detalle) {
                                        $producto = $detalle->producto;

                                        return [
                                            'tipo_linea' => 'insumo',
                                            'producto_id' => $detalle->producto_id,
                                            'cantidad' => $detalle->cantidad,
                                            'costo_unitario' => CostoService::resolveCostoUnitario($producto),
                                        ];
                                    })->toArray();

                                    $set('detalles', $detalles);
                                }
                            })
                            ->placeholder('Seleccione un producto para detectar la fórmula')
                            ->helperText('Las fórmulas son obligatorias para todas las transformaciones.')
                            ->columnSpanFull(),
                    ]),

                Hidden::make('_producto_tiene_stock')
                    ->default(false)
                    ->dehydrated(false),

                Section::make('Precio y Costo')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Select::make('tipo_calculo_precio')
                            ->label('Cálculo del precio')
                            ->options(TipoCalculoPrecio::class)
                            ->default(fn (Get $get) => TipoCalculoPrecio::defaultForTipo(
                                TransformacionTipo::tryFrom($get('tipo')?->value ?? $get('tipo') ?? '') ?? TransformacionTipo::FABRICACION
                            )->value)
                            ->helperText(function (Get $get): string {
                                $v = $get('tipo_calculo_precio');
                                $raw = $v instanceof TipoCalculoPrecio ? $v->value : $v;

                                if ($get('_producto_tiene_stock')) {
                                    return 'Producto ya tiene stock — el precio de venta no se modificará';
                                }

                                return match ($raw) {
                                    'margen' => TipoCalculoPrecio::MARGEN->description(),
                                    'manual' => TipoCalculoPrecio::MANUAL->description(),
                                    default => ''
                                };
                            })
                            ->live()
                            ->disabled(fn (Get $get): bool => (bool) $get('_producto_tiene_stock'))
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                $raw = $state instanceof TipoCalculoPrecio ? $state->value : (string) $state;
                                $tipo = TransformacionTipo::tryFrom($get('tipo')?->value ?? $get('tipo') ?? '') ?? TransformacionTipo::FABRICACION;
                                if ($raw === 'margen') {
                                    $set('margen_deseado', TipoCalculoPrecio::defaultMargenForTipo($tipo));
                                }
                            })
                            ->columnSpan(1),
                        TextInput::make('costo_total')
                            ->label('Costo total')
                            ->numeric()
                            ->disabled()
                            ->dehydrated()
                            ->prefix('$')
                            ->helperText('Se calculará automáticamente al confirmar')
                            ->hiddenOn('create')
                            ->columnSpan(1),
                        TextInput::make('margen_deseado')
                            ->label('Margen de ganancia')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.01)
                            ->suffix('%')
                            ->default(fn (Get $get) => TipoCalculoPrecio::defaultMargenForTipo(
                                TransformacionTipo::tryFrom($get('tipo')?->value ?? $get('tipo') ?? '') ?? TransformacionTipo::FABRICACION
                            ))
                            ->disabled(fn (Get $get): bool => (bool) $get('_producto_tiene_stock'))
                            ->helperText(fn (Get $get): string => $get('_producto_tiene_stock')
                                ? 'Valor de referencia de la última transformación'
                                : 'El precio sugerido se calculará automáticamente'
                            )
                            ->visible(function (Get $get): bool {
                                $v = $get('tipo_calculo_precio');

                                return ($v instanceof TipoCalculoPrecio ? $v->value : $v) === 'margen';
                            })
                            ->columnSpan(1),
                        TextInput::make('precio_sugerido')
                            ->label('Precio sugerido')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('$')
                            ->disabled(fn (Get $get): bool => (bool) $get('_producto_tiene_stock'))
                            ->helperText(fn (Get $get): string => $get('_producto_tiene_stock')
                                ? 'Valor de referencia de la última transformación'
                                : 'Ingrese el precio de venta deseado'
                            )
                            ->visible(function (Get $get): bool {
                                $v = $get('tipo_calculo_precio');

                                return ($v instanceof TipoCalculoPrecio ? $v->value : $v) === 'manual';
                            })
                            ->columnSpan(1),
                    ]),

                Section::make('Componentes / Insumos')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('detalles')
                            ->relationship('detalles')
                            ->columns(6)
                            ->addable(false)
                            ->deletable(false)
                            ->saved(false)
                            ->schema([
                                Select::make('tipo_linea')
                                    ->label('Tipo')
                                    ->options(TransformacionLineaTipo::class)
                                    ->default(TransformacionLineaTipo::INSUMO->value)
                                    ->required()
                                    ->disabled()
                                    ->dehydrated(true),
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
                                    ->disabled()
                                    ->dehydrated(true),
                                TextInput::make('lote')
                                    ->label('Lote')
                                    ->columnSpan(1)
                                    ->disabled()
                                    ->dehydrated(true),
                                TextInput::make('cantidad')
                                    ->label('Cantidad')
                                    ->numeric()
                                    ->default(1)
                                    ->required()
                                    ->disabled()
                                    ->dehydrated(true),
                                TextInput::make('costo_unitario')
                                    ->label('Costo Unit.')
                                    ->numeric()
                                    ->prefix('$')
                                    ->disabled()
                                    ->dehydrated(true),
                            ])
                            ->defaultItems(0)
                            ->columnSpanFull()
                            ->helperText('Los insumos se cargan automáticamente desde la fórmula.'),
                    ]),

                Section::make('Observaciones')
                    ->schema([
                        Textarea::make('observaciones')
                            ->label('Observaciones'),
                    ])->columnSpanFull(),
            ]);
    }

    /**
     * Formulario rápido para crear un producto desde la transformación.
     */
    private static function quickCreateProductoForm(string $tipoProductoDefault, ?string $tipo = null): array
    {
        $enumTipo = $tipo ? TransformacionTipo::tryFrom($tipo) : null;
        $categoriaDefault = $enumTipo?->categoriaSugerida()?->id;
        $marcaDefault = $enumTipo?->marcaSugerida()?->id;

        return [
            TextInput::make('codigo')
                ->label('Código / SKU')
                ->required()
                ->unique('productos', 'codigo')
                ->maxLength(50),
            TextInput::make('nombre')
                ->label('Nombre')
                ->required()
                ->maxLength(150),
            Select::make('tipo_producto')
                ->label('Tipo de Producto')
                ->options([
                    'comprado' => 'Comprado',
                    'manufacturado' => 'Manufacturado',
                    'materia_prima' => 'Materia Prima',
                    'servicio' => 'Servicio',
                ])
                ->default($tipoProductoDefault)
                ->required(),
            Select::make('categoria_id')
                ->label('Categoría')
                ->options(Categoria::where('activo', true)->pluck('nombre', 'id'))
                ->searchable()
                ->default($categoriaDefault)
                ->helperText(fn (): ?string => $categoriaDefault
                    ? "Sugerida según tipo de transformación"
                    : null
                ),
            Select::make('marca_id')
                ->label('Marca')
                ->options(Marca::where('activo', true)->pluck('nombre', 'id'))
                ->searchable()
                ->default($marcaDefault)
                ->helperText(fn (): ?string => $marcaDefault
                    ? "Sugerida según tipo de transformación"
                    : null
                ),
            Select::make('unidad_medida_id')
                ->label('Unidad de Medida')
                ->options(UnidadMedida::where('activo', true)->pluck('nombre', 'id'))
                ->searchable(),
            Select::make('impuesto_id')
                ->label('Impuesto')
                ->options(Impuesto::where('activo', true)->pluck('nombre', 'id'))
                ->searchable()
                ->disabled(! Empresa::isResponsableIva())
                ->default(function () {
                    if (! Empresa::isResponsableIva()) {
                        return Impuesto::where('tipo', 'IVA')->where('porcentaje', 0)->where('activo', true)->value('id');
                    }

                    return null;
                }),
            TextInput::make('precio_venta')
                ->label('Precio de Venta')
                ->numeric()
                ->prefix('$'),
            Toggle::make('activo')
                ->label('Activo')
                ->default(true),
        ];
    }
}
