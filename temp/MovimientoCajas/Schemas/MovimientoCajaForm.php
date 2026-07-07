<?php

namespace App\Filament\Resources\MovimientoCajas\Schemas;

use App\Enums\BancoEstado;
use App\Enums\CajaCategoria;
use App\Enums\TrasladoDestinoTipo;
use App\Models\Banco;
use App\Models\Caja;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class MovimientoCajaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('caja_id')
                    ->label('Caja')
                    ->options(Caja::where('estado', 'activa')->pluck('nombre', 'id'))
                    ->required()
                    ->searchable(),
                Select::make('tipo')
                    ->label('Tipo de Movimiento')
                    ->options([
                        'ingreso' => 'Ingreso',
                        'egreso' => 'Egreso',
                        'traslado' => 'Traslado',
                    ])
                    ->live()
                    ->required(),
                Select::make('traslado_destino_tipo')
                    ->label('Tipo de Destino')
                    ->options(collect(TrasladoDestinoTipo::cases())->mapWithKeys(
                        fn ($case) => [$case->value => $case->label()]
                    ))
                    ->visible(fn (Get $get) => $get('tipo') === 'traslado')
                    ->required(fn (Get $get) => $get('tipo') === 'traslado')
                    ->live(),
                Select::make('traslado_destino_id')
                    ->label('Destino')
                    ->options(fn (Get $get) => match ($get('traslado_destino_tipo')) {
                        'caja' => Caja::where('estado', 'activa')
                            ->where('id', '!=', (int) $get('caja_id'))
                            ->pluck('nombre', 'id'),
                        'banco' => Banco::where('estado', BancoEstado::ACTIVO)->pluck('nombre_banco', 'id'),
                        default => [],
                    })
                    ->visible(fn (Get $get) => $get('tipo') === 'traslado' && $get('traslado_destino_tipo'))
                    ->required(fn (Get $get) => $get('tipo') === 'traslado' && $get('traslado_destino_tipo'))
                    ->searchable(),
                TextInput::make('monto')
                    ->label('Monto')
                    ->numeric()
                    ->prefix('$')
                    ->required(),
                Select::make('categoria')
                    ->label('Categoría')
                    ->options(collect(CajaCategoria::cases())->mapWithKeys(
                        fn ($case) => [$case->value => $case->label()]
                    ))
                    ->visible(fn (Get $get) => $get('tipo') !== 'traslado')
                    ->required(fn (Get $get) => in_array($get('tipo'), ['ingreso', 'egreso']))
                    ->searchable(),
                TextInput::make('referencia')
                    ->label('Referencia')
                    ->maxLength(100)
                    ->nullable(),
                TextInput::make('concepto')
                    ->label('Concepto')
                    ->maxLength(255)
                    ->nullable(),
                DateTimePicker::make('fecha_movimiento')
                    ->label('Fecha del Movimiento')
                    ->default(now()),
            ]);
    }
}
