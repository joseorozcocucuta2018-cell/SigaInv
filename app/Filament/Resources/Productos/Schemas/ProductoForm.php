<?php

declare(strict_types=1);

namespace App\Filament\Resources\Productos\Schemas;

use App\Enums\ProductoTipo;
use App\Filament\Resources\Empresas\EmpresaResource;
use App\Models\Categoria;
use App\Models\Empresa;
use App\Models\Impuesto;
use App\Models\Marca;
use App\Models\UnidadMedida;
use App\Services\PrecioService;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class ProductoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identificación')
                    ->columns(2)
                    ->schema([
                        TextInput::make('codigo')
                            ->label('Código / SKU')
                            ->placeholder('Se genera automáticamente')
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            ->disabled()
                            ->dehydrated(true),
                        TextInput::make('codigo_barras')
                            ->label('Código de Barras')
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),
                        TextInput::make('nombre')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(150)
                            ->columnSpanFull()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('nombre', mb_convert_case(trim($state ?? ''), MB_CASE_TITLE, 'UTF-8'))),
                        TextInput::make('nombre_comun')
                            ->label('Nombre Común')
                            ->maxLength(150)
                            ->columnSpanFull()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('nombre_comun', mb_convert_case(trim($state ?? ''), MB_CASE_TITLE, 'UTF-8'))),
                    ]),

                Section::make('Clasificación')
                    ->columns(2)
                    ->schema([
                        Select::make('tipo_producto')
                            ->label('Tipo de Producto')
                            ->options(ProductoTipo::class)
                            ->default('comprado')
                            ->required(),
                        Select::make('categoria_id')
                            ->label('Categoría')
                            ->options(fn () => Categoria::where('activo', true)->pluck('nombre', 'id'))
                            ->searchable()
                            ->required()
                            ->placeholder('Seleccionar')
                            ->createOptionForm([
                                TextInput::make('nombre')
                                    ->required()
                                    ->maxLength(100),
                            ])
                            ->createOptionUsing(fn (array $data) => Categoria::firstOrCreate(['nombre' => $data['nombre']], ['activo' => true])->id),
                        Select::make('marca_id')
                            ->label('Marca')
                            ->options(fn () => Marca::where('activo', true)->pluck('nombre', 'id'))
                            ->searchable()
                            ->required()
                            ->placeholder('Seleccionar')
                            ->createOptionForm([
                                TextInput::make('nombre')
                                    ->required()
                                    ->maxLength(100),
                            ])
                            ->createOptionUsing(fn (array $data) => Marca::firstOrCreate(['nombre' => $data['nombre']], ['activo' => true])->id),
                        Select::make('unidad_medida_id')
                            ->label('Unidad de Medida')
                            ->options(fn () => UnidadMedida::where('activo', true)->pluck('nombre', 'id'))
                            ->searchable()
                            ->required()
                            ->placeholder('Seleccionar')
                            ->createOptionForm([
                                TextInput::make('nombre')
                                    ->label('Nombre')
                                    ->required()
                                    ->maxLength(60),
                                TextInput::make('simbolo')
                                    ->label('Símbolo')
                                    ->required()
                                    ->maxLength(20),
                            ])
                            ->createOptionUsing(fn (array $data) => UnidadMedida::firstOrCreate(
                                ['nombre' => trim($data['nombre'])],
                                ['simbolo' => trim($data['simbolo'] ?? ''), 'activo' => true]
                            )->id),
                        Select::make('impuesto_id')
                            ->label('Impuesto')
                            ->options(Impuesto::where('activo', true)->pluck('nombre', 'id'))
                            ->searchable()
                            ->required()
                            ->placeholder('Seleccionar')
                            ->disabled(! Empresa::isResponsableIva())
                            ->default(function () {
                                if (! Empresa::isResponsableIva()) {
                                    return Impuesto::where('tipo', 'IVA')->where('porcentaje', 0)->where('activo', true)->value('id');
                                }

                                return null;
                            }),
                    ]),

                Section::make('Precios y Rentabilidad')
                    ->columns(3)
                    ->schema([
                        TextInput::make('precio_compra')
                            ->label('Precio de Compra')
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (?string $state, Set $set) {
                                if (! $state) {
                                    return;
                                }

                                $precioVenta = app(PrecioService::class)->calcularPrecioVentaSugerido((float) $state);
                                $set('precio_venta', $precioVenta);
                            }),
                        TextInput::make('precio_venta')
                            ->label('Precio de Venta')
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->helperText(fn () => 'Margen sugerido: '.(Empresa::first()?->margen_ganancia_default ?? 0).'%')
                            ->live(onBlur: true)
                            ->rules([
                                fn (Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                    if (! $get('permitir_venta_bajo_costo') && (float) $value < (float) $get('precio_compra')) {
                                        $fail('El precio de venta no puede ser menor al precio de compra a menos que se autorice la venta bajo costo.');
                                    }
                                },
                            ]),
                        Placeholder::make('utilidad_estimada')
                            ->label('Utilidad Estimada')
                            ->content(function (Get $get): string {
                                $compra = (float) $get('precio_compra');
                                $venta = (float) $get('precio_venta');
                                if ($venta <= 0) {
                                    return '0%';
                                }
                                $utilidad = (($venta - $compra) / $venta) * 100;

                                return number_format($utilidad, 1).'%';
                            }),
                        Toggle::make('permitir_venta_bajo_costo')
                            ->label('Autorizar venta bajo costo')
                            ->helperText('Solo un SuperAdministrador puede autorizar liquidaciones o precios bajo el costo.')
                            ->columnSpanFull()
                            ->default(false)
                            ->live()
                            ->dehydrated(false)
                            ->visible(fn () => EmpresaResource::isSuperAdmin()),
                    ]),

                Section::make('Stock')
                    ->columns(2)
                    ->schema([
                        TextInput::make('stock_minimo')
                            ->label('Stock Mínimo')
                            ->numeric()
                            ->default(0),
                        TextInput::make('stock_maximo')
                            ->label('Stock Máximo')
                            ->numeric()
                            ->default(0),
                        Toggle::make('exige_lote')
                            ->label('Controlar por lote')
                            ->helperText('Si está activo, el sistema exigirá lote y vencimiento al mover este producto.')
                            ->default(false),
                        Toggle::make('exige_serial')
                            ->label('Controlar por serial')
                            ->helperText('Disponible solo si la empresa tiene activo el uso de seriales.')
                            ->visible(fn (Get $get) => (bool) Empresa::first()?->usa_seriales)
                            ->default(false),
                    ]),

                Section::make('Imagen')
                    ->schema([
                        FileUpload::make('imagen')
                            ->label('Imagen del Producto')
                            ->image()
                            ->disk('directo')
                            ->visibility('public')
                            ->directory('productos')
                            ->maxSize(2048),
                    ]),
            ]);
    }

    /**
     * Componentes para creación rápida desde un Select
     */
    public static function getQuickCreateComponents(): array
    {
        return [
            TextInput::make('nombre')
                ->label('Nombre')
                ->required()
                ->maxLength(150)
                ->live(onBlur: true)
                ->afterStateUpdated(fn (Set $set, ?string $state) => $set('nombre', mb_convert_case(trim($state ?? ''), MB_CASE_TITLE, 'UTF-8'))),
            TextInput::make('nombre_comun')
                ->label('Nombre Común')
                ->maxLength(150)
                ->live(onBlur: true)
                ->afterStateUpdated(fn (Set $set, ?string $state) => $set('nombre_comun', mb_convert_case(trim($state ?? ''), MB_CASE_TITLE, 'UTF-8'))),
            TextInput::make('codigo_barras')
                ->label('Código de Barras')
                ->maxLength(50),
            Select::make('tipo_producto')
                ->label('Tipo de Producto')
                ->options(ProductoTipo::class)
                ->default('comprado')
                ->required(),
            Select::make('categoria_id')
                ->label('Categoría')
                ->options(fn () => Categoria::where('activo', true)->pluck('nombre', 'id'))
                ->searchable()
                ->required()
                ->default(1)
                ->placeholder('Seleccionar')
                ->createOptionForm([
                    TextInput::make('nombre')
                        ->required()
                        ->maxLength(100),
                ])
                ->createOptionUsing(fn (array $data) => Categoria::firstOrCreate(
                    ['nombre' => mb_convert_case(trim($data['nombre']), MB_CASE_TITLE, 'UTF-8')],
                    ['activo' => true]
                )->id),
            Select::make('marca_id')
                ->label('Marca')
                ->options(fn () => Marca::where('activo', true)->pluck('nombre', 'id'))
                ->searchable()
                ->required()
                ->default(1)
                ->placeholder('Seleccionar')
                ->createOptionForm([
                    TextInput::make('nombre')
                        ->required()
                        ->maxLength(100),
                ])
                ->createOptionUsing(fn (array $data) => Marca::firstOrCreate(
                    ['nombre' => mb_convert_case(trim($data['nombre']), MB_CASE_TITLE, 'UTF-8')],
                    ['activo' => true]
                )->id),
            Select::make('unidad_medida_id')
                ->label('Unidad de Medida')
                ->options(fn () => UnidadMedida::where('activo', true)->pluck('nombre', 'id'))
                ->required()
                ->default(1)
                ->searchable()
                ->createOptionForm([
                    TextInput::make('nombre')
                        ->label('Nombre')
                        ->required()
                        ->maxLength(60),
                    TextInput::make('simbolo')
                        ->label('Símbolo')
                        ->required()
                        ->maxLength(20),
                ])
                ->createOptionUsing(fn (array $data) => UnidadMedida::firstOrCreate(
                    ['nombre' => trim($data['nombre'])],
                    ['simbolo' => trim($data['simbolo'] ?? ''), 'activo' => true]
                )->id),
            TextInput::make('precio_compra')
                ->label('Precio de Compra')
                ->numeric()
                ->prefix('$')
                ->required()
                ->default(0)
                ->live(onBlur: true)
                ->afterStateUpdated(function (?string $state, Set $set) {
                    if (! $state) {
                        return;
                    }

                    $precioVenta = app(PrecioService::class)->calcularPrecioVentaSugerido((float) $state);
                    $set('precio_venta', $precioVenta);
                }),
            TextInput::make('precio_venta')
                ->label('Precio de Venta (Sugerido)')
                ->numeric()
                ->prefix('$')
                ->dehydrated(true)
                ->helperText(fn () => 'Calculado con el margen del '.(Empresa::first()?->margen_ganancia_default ?? 0).'%')
                ->default(0),
            FileUpload::make('imagen')
                ->label('Imagen del Producto')
                ->image()
                ->disk('directo')
                ->visibility('public')
                ->directory('productos')
                ->maxSize(2048),
        ];
    }
}
