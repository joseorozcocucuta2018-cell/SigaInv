<?php

namespace App\Filament\Resources\Cajas\Schemas;

use App\Enums\CajaEstado;
use App\Enums\CajaTipo;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CajaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nombre')
                    ->label('Nombre de la Caja')
                    ->required()
                    ->maxLength(100),
                Select::make('tipo')
                    ->label('Tipo de Caja')
                    ->options(collect(CajaTipo::cases())->mapWithKeys(
                        fn ($case) => [$case->value => $case->label()]
                    ))
                    ->default(CajaTipo::GENERAL->value)
                    ->required(),
                TextInput::make('saldo_inicial')
                    ->label('Saldo Inicial')
                    ->numeric()
                    ->default(0)
                    ->prefix('$'),
                Select::make('estado')
                    ->label('Estado')
                    ->options(CajaEstado::class)
                    ->default(CajaEstado::ACTIVA)
                    ->required(),
            ]);
    }
}
