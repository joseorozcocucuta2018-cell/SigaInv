<?php

declare(strict_types=1);

namespace App\Filament\Resources\FormulaTransformacions\Schemas;

use App\Enums\ProductoTipo;
use App\Enums\TransformacionTipo;
use App\Filament\Resources\Productos\Schemas\ProductoForm;
use App\Models\Categoria;
use App\Models\FormulaTransformacion;
use App\Models\Producto;
use App\Models\UnidadMedida;
use App\Services\ProductoService;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class FormulaTransformacionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Fórmula')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Select::make('tipo')
                            ->label('Tipo')
                            ->options(TransformacionTipo::class)
                            ->default('fabricacion')
                            ->required()
                            ->live()
                            ->columnSpan(1),

                        TextInput::make('producto_final_nombre')
                            ->label('Nombre del Producto Final')
                            ->required()
                            ->maxLength(150)
                            ->rules(function (TextInput $component): array {
                                return [
                                    function (string $attribute, mixed $value, \Closure $fail) use ($component): void {
                                        $query = FormulaTransformacion::where('producto_final_nombre', $value);
                                        $record = $component->getRecord();
                                        if ($record?->exists) {
                                            $query->where('id', '!=', $record->id);
                                        }
                                        if ($query->exists()) {
                                            $fail('Ya existe una fórmula con este nombre.');
                                        }
                                    },
                                ];
                            })
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('producto_final_nombre', mb_strtoupper($state ?? '')))
                            ->columnSpan(1)
                            ->helperText('Se creará automáticamente como producto manufacturado'),

                        TextEntry::make('productoFinal.nombre')
                            ->label('Producto Final')
                            ->url(fn ($record) => $record?->producto_final_id ? route('filament.admin.resources.productos.edit', ['record' => $record->producto_final_id]) : null)
                            ->openUrlInNewTab()
                            ->placeholder('Sin producto asignado')
                            ->columnSpan(1)
                            ->visible(fn (?FormulaTransformacion $record) => $record !== null),

                        TextInput::make('cantidad_producto_final')
                            ->label('Cantidad por Fórmula')
                            ->numeric()
                            ->minValue(0.001)
                            ->default(1)
                            ->required()
                            ->helperText('Unidades que genera cada ejecución')
                            ->columnSpan(1),
                        Toggle::make('activo')
                            ->label('Activo')
                            ->default(true)
                            ->hiddenOn('create')
                            ->columnSpan(1),

                        Textarea::make('descripcion')
                            ->label('Descripción')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),

                Section::make('Insumos')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('detalles')
                            ->relationship()
                            ->columns(4)
                            ->schema([
                                Select::make('producto_id')
                                    ->label('Insumo')
                                    ->options(function (Get $get): array {
                                        $query = Producto::where('activo', true)
                                            ->where('tipo_producto', '!=', ProductoTipo::MANUFACTURADO->value);
                                        $productoFinalId = $get('../../producto_final_id');
                                        if ($productoFinalId) {
                                            $query->where('id', '!=', $productoFinalId);
                                        }

                                        return $query->pluck('nombre', 'id')
                                            ->mapWithKeys(fn ($nombre, $id) => [
                                                $id => strlen($nombre) > 50 ? mb_substr($nombre, 0, 50).'…' : $nombre,
                                            ])->toArray();
                                    })
                                    ->searchable()
                                    ->required()
                                    ->columnSpan(3)
                                    ->createOptionForm(ProductoForm::getQuickCreateComponents())
                                    ->createOptionUsing(fn (array $data) => ProductoService::crear($data)->id),
                                TextInput::make('cantidad')
                                    ->label('Cantidad')
                                    ->numeric()
                                    ->minValue(0.001)
                                    ->default(1)
                                    ->required()
                                    ->columnSpan(1),
                            ])
                            ->defaultItems(1)
                            ->addActionLabel('Agregar insumo')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    /**
     * Formulario rápido para crear insumos desde el repeater.
     */
    public static function quickCreateInsumoForm(): array
    {
        return [
            TextInput::make('nombre')
                ->label('Nombre')
                ->required()
                ->maxLength(150),
            Select::make('categoria_id')
                ->label('Categoría')
                ->options(Categoria::where('activo', true)->pluck('nombre', 'id'))
                ->searchable(),
            Select::make('unidad_medida_id')
                ->label('Unidad de Medida')
                ->options(UnidadMedida::where('activo', true)->pluck('nombre', 'id'))
                ->searchable()
                ->required(),
        ];
    }
}
