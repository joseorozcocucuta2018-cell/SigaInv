<?php

declare(strict_types=1);

namespace App\Filament\Resources\AjusteInventarios\Schemas;

use App\Enums\MotivoAjuste;
use App\Models\Producto;
use App\Services\BodegaService;
use App\Services\CostoService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class AjusteInventarioForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información General')
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        Select::make('bodega_id')
                            ->label('Bodega')
                            ->relationship('bodega', 'nombre')
                            ->required()
                            ->live()
                            ->default(fn () => BodegaService::bodegaDefaultId())
                            ->disabled(BodegaService::bodegaDeshabilitada() || fn ($operation) => $operation === 'edit')
                            ->dehydrated(true),

                        Select::make('motivo')
                            ->label('Motivo')
                            ->options(collect(MotivoAjuste::cases())
                                ->reject(fn (MotivoAjuste $m) => in_array($m, [MotivoAjuste::AJUSTE_INICIAL, MotivoAjuste::OTRO]))
                                ->mapWithKeys(fn (MotivoAjuste $m) => [$m->value => $m->label()])
                                ->toArray())
                            ->required()
                            ->live(),

                        DatePicker::make('fecha')
                            ->label('Fecha')
                            ->required()
                            ->default(now()),

                        Textarea::make('observacion')
                            ->label('Observación')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->disabled(fn ($record) => $record?->estado && ! $record->estado->isEditable()),

                Section::make('Productos a Ajustar')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('detalles')
                            ->relationship('detalles')
                            ->label('Detalle del Ajuste')
                            ->columns(4)
                            ->schema([
                                Select::make('producto_id')
                                    ->label('Producto')
                                    ->options(Producto::where('activo', true)->pluck('nombre', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->columnSpan(2),

                                TextInput::make('stock_fisico')
                                    ->label('Cantidad')
                                    ->numeric()
                                    ->minValue(0.001)
                                    ->required()
                                    ->columnSpan(1)
                                    ->helperText(fn (Get $get): string => $get('../../motivo') === 'conteo_fisico'
                                        ? 'Se sumará al stock actual'
                                        : 'Se restará del stock actual'),
                            ])
                            ->addActionLabel('Agregar Producto')
                            ->reorderable(false)
                            ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                                $data['costo_unitario'] = CostoService::resolveCostoUnitario(
                                    Producto::find($data['producto_id'])
                                );

                                return $data;
                            })
                            ->mutateRelationshipDataBeforeSaveUsing(function (array $data): array {
                                $data['costo_unitario'] = CostoService::resolveCostoUnitario(
                                    Producto::find($data['producto_id'])
                                );

                                return $data;
                            }),
                    ])
                    ->disabled(fn ($record) => $record?->estado && ! $record->estado->isEditable()),
            ]);
    }
}
