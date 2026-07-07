<?php

declare(strict_types=1);

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
                    ->options(CajaTipo::class)
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
