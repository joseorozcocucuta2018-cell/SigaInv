<?php

declare(strict_types=1);

namespace App\Filament\Resources\Bodegas\Schemas;

use App\Enums\BodegaEstado;
use App\Models\Ciudad;
use App\Models\Departamento;
use App\Models\Empresa;
use App\Services\BodegaService;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Collection;

class BodegaForm
{
    public static function configure(Schema $schema): Schema
    {
        $unaSolaBodega = BodegaService::usaUnaSolaBodega();

        if ($unaSolaBodega) {
            $empresa = Empresa::actual();

            return $schema
                ->components([
                    TextInput::make('nombre')
                        ->label('Nombre')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(100)
                        ->disabled()
                        ->dehydrated(),
                    Section::make('Ubicación — tomada de la empresa')
                        ->description('Edita los datos de la empresa para cambiar esta ubicación.')
                        ->compact()
                        ->schema([
                            Placeholder::make('direccion1')
                                ->label('Dirección')
                                ->content(fn (): string => $empresa?->direccion ?? '—'),
                            Placeholder::make('departamento_id')
                                ->label('Departamento')
                                ->content(fn (): string => $empresa?->departamento?->nombre ?? '—'),
                            Placeholder::make('ciudad_id')
                                ->label('Ciudad')
                                ->content(fn (): string => $empresa?->ciudad?->nombre ?? '—'),
                        ])
                        ->columns(3),
                    Select::make('numero_cajas_pos')
                        ->label('Puntos de venta (POS)')
                        ->helperText('Crea automáticamente esta cantidad de cajas POS para esta bodega.')
                        ->options(array_combine(range(0, 5), range(0, 5)))
                        ->default(0),
                    Select::make('estado')
                        ->label('Estado')
                        ->options(BodegaEstado::class)
                        ->default(BodegaEstado::ACTIVO)
                        ->disabled()
                        ->dehydrated(),
                ]);
        }

        return $schema
            ->components([
                TextInput::make('nombre')
                    ->label('Nombre')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(100),
                Textarea::make('descripcion')
                    ->label('Descripción')
                    ->columnSpanFull(),
                TextInput::make('direccion1')
                    ->label('Dirección')
                    ->required()
                    ->maxLength(100),
                TextInput::make('direccion2')
                    ->label('Dirección 2')
                    ->maxLength(100),
                Select::make('departamento_id')
                    ->label('Departamento')
                    ->options(Departamento::orderBy('nombre')->pluck('nombre', 'id'))
                    ->searchable()
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Set $set) => $set('ciudad_id', null)),
                Select::make('ciudad_id')
                    ->label('Ciudad')
                    ->options(fn (Get $get): Collection => Ciudad::where('departamento_id', $get('departamento_id'))
                        ->orderBy('nombre')
                        ->pluck('nombre', 'id'))
                    ->searchable()
                    ->required()
                    ->createOptionForm([
                        TextInput::make('nombre')
                            ->required()
                            ->maxLength(100),
                    ])
                    ->createOptionUsing(function (array $data, Get $get): int {
                        return Ciudad::create([
                            'nombre' => strtoupper($data['nombre']),
                            'departamento_id' => $get('departamento_id'),
                        ])->id;
                    })
                    ->visible(fn (Get $get) => filled($get('departamento_id'))),
                Toggle::make('es_principal')
                    ->label('Bodega Principal')
                    ->helperText('Esta bodega será la predeterminada para operaciones.'),
                Select::make('numero_cajas_pos')
                    ->label('Puntos de venta (POS)')
                    ->helperText('Crea automáticamente esta cantidad de cajas POS para esta bodega.')
                    ->options(array_combine(range(0, 5), range(0, 5)))
                    ->default(0),
                Select::make('estado')
                    ->label('Estado')
                    ->options(BodegaEstado::class)
                    ->default(BodegaEstado::ACTIVO)
                    ->required(),
            ]);
    }
}
