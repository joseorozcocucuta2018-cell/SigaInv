<?php

declare(strict_types=1);

namespace App\Filament\Resources\MovimientoCajas\Schemas;

use App\Enums\CajaCategoria;
use App\Enums\CajaEstado;
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
                Select::make('tipo')
                    ->label('Tipo de Movimiento')
                    ->options([
                        'ingreso' => 'Ingreso',
                        'egreso' => 'Egreso',
                        'traslado' => 'Traslado',
                        'consignacion' => 'Consignación',
                    ])
                    ->live()
                    ->required(),
                Select::make('caja_id')
                    ->label('Caja Origen')
                    ->options(function (Get $get) {
                        $cajas = Caja::where('estado', CajaEstado::ACTIVA->value)->get();

                        if (in_array($get('tipo'), ['egreso', 'traslado', 'consignacion'], true)) {
                            $cajas = $cajas->filter(fn ($c) => $c->saldo_actual > 0);
                        }

                        return $cajas->pluck('nombre', 'id')->toArray();
                    })
                    ->searchable()
                    ->required()
                    ->visible(fn (Get $get) => filled($get('tipo'))),
                Select::make('destino_id')
                    ->label(fn (Get $get) => match ($get('tipo')) {
                        'traslado' => 'Caja Destino',
                        'consignacion' => 'Banco Destino',
                        default => 'Destino',
                    })
                    ->options(fn (Get $get) => match ($get('tipo')) {
                        'traslado' => Caja::where('estado', CajaEstado::ACTIVA->value)
                            ->where('id', '!=', (int) $get('caja_id'))
                            ->pluck('nombre', 'id')
                            ->toArray(),
                        'consignacion' => Banco::where('estado', 'activo')->pluck('nombre_banco', 'id')->toArray(),
                        default => [],
                    })
                    ->searchable()
                    ->visible(fn (Get $get) => in_array($get('tipo'), ['traslado', 'consignacion'], true))
                    ->required(fn (Get $get) => in_array($get('tipo'), ['traslado', 'consignacion'], true)),
                TextInput::make('monto')
                    ->label('Monto')
                    ->numeric()
                    ->prefix('$')
                    ->required(),
                Select::make('categoria')
                    ->label('Categoría')
                    ->options(fn (Get $get) => collect(CajaCategoria::cases())
                        ->filter(fn (CajaCategoria $cat) => $cat->tipoMovimiento() === $get('tipo'))
                        ->mapWithKeys(fn (CajaCategoria $cat) => [$cat->value => $cat->label()])
                        ->toArray())
                    ->visible(fn (Get $get) => in_array($get('tipo'), ['ingreso', 'egreso'], true))
                    ->required(fn (Get $get) => in_array($get('tipo'), ['ingreso', 'egreso'], true))
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
