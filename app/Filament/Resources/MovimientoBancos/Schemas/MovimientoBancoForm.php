<?php

declare(strict_types=1);

namespace App\Filament\Resources\MovimientoBancos\Schemas;

use App\Enums\BancoEstado;
use App\Enums\CajaEstado;
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
                Select::make('tipo')
                    ->label('Tipo de Movimiento')
                    ->options([
                        'deposito' => 'Depósito / Entrada',
                        'retiro' => 'Retiro / Salida',
                        'transferencia' => 'Transferencia',
                    ])
                    ->required()
                    ->live(),
                Select::make('banco_id')
                    ->label('Banco / Cuenta Origen')
                    ->options(function (Get $get) {
                        $bancos = Banco::where('estado', BancoEstado::ACTIVO)->get();

                        if (in_array($get('tipo'), ['retiro', 'transferencia'], true)) {
                            $bancos = $bancos->filter(fn ($b) => $b->saldo_actual > 0);
                        }

                        return $bancos->pluck('nombre_banco', 'id')->toArray();
                    })
                    ->searchable()
                    ->required()
                    ->visible(fn (Get $get) => filled($get('tipo'))),
                Select::make('destino_id')
                    ->label(fn (Get $get) => match ($get('tipo')) {
                        'transferencia' => 'Banco Destino',
                        'retiro' => 'Caja Destino',
                        default => 'Destino',
                    })
                    ->options(fn (Get $get) => match ($get('tipo')) {
                        'transferencia' => Banco::where('estado', BancoEstado::ACTIVO)
                            ->where('id', '!=', (int) $get('banco_id'))
                            ->pluck('nombre_banco', 'id')
                            ->toArray(),
                        'retiro' => Caja::where('estado', CajaEstado::ACTIVA->value)->pluck('nombre', 'id')->toArray(),
                        default => [],
                    })
                    ->searchable()
                    ->visible(fn (Get $get) => in_array($get('tipo'), ['transferencia', 'retiro'], true))
                    ->required(fn (Get $get) => in_array($get('tipo'), ['transferencia', 'retiro'], true)),
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
