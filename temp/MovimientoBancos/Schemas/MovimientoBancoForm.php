<?php

namespace App\Filament\Resources\MovimientoBancos\Schemas;

use App\Enums\BancoEstado;
use App\Enums\MovimientoBancoTipo;
use App\Enums\TrasladoDestinoTipo;
use App\Models\Banco;
use App\Models\Caja;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class MovimientoBancoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('banco_id')
                    ->label('Banco / Cuenta')
                    ->options(Banco::where('estado', BancoEstado::ACTIVO)->pluck('nombre_banco', 'id'))
                    ->required()
                    ->searchable(),
                Select::make('tipo')
                    ->label('Tipo de Movimiento')
                    ->options(collect(MovimientoBancoTipo::cases())->mapWithKeys(
                        fn ($case) => [$case->value => $case->label()]
                    ))
                    ->required()
                    ->live(),
                Select::make('traslado_destino_tipo')
                    ->label('Tipo de Destino')
                    ->options(collect(TrasladoDestinoTipo::cases())->mapWithKeys(
                        fn ($case) => [$case->value => $case->label()]
                    ))
                    ->visible(fn (Get $get) => $get('tipo') === 'transferencia')
                    ->required(fn (Get $get) => $get('tipo') === 'transferencia')
                    ->live(),
                Select::make('traslado_destino_id')
                    ->label('Destino')
                    ->options(fn (Get $get) => match ($get('traslado_destino_tipo')) {
                        'caja' => Caja::where('estado', 'activa')->pluck('nombre', 'id'),
                        'banco' => Banco::where('estado', BancoEstado::ACTIVO)
                            ->where('id', '!=', (int) $get('banco_id'))
                            ->pluck('nombre_banco', 'id'),
                        default => [],
                    })
                    ->visible(fn (Get $get) => $get('tipo') === 'transferencia' && $get('traslado_destino_tipo'))
                    ->required(fn (Get $get) => $get('tipo') === 'transferencia' && $get('traslado_destino_tipo'))
                    ->searchable(),
                TextInput::make('monto')
                    ->label('Monto')
                    ->numeric()
                    ->prefix('$')
                    ->required(),
                TextInput::make('referencia')
                    ->label('Referencia / Nro. Operación')
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
