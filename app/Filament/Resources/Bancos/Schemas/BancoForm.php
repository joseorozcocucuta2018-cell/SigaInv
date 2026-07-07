<?php

declare(strict_types=1);

namespace App\Filament\Resources\Bancos\Schemas;

use App\Enums\BancoEstado;
use App\Enums\BancoTipoCuenta;
use App\Models\Banco;
use App\Models\MovimientoBanco;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BancoForm
{
    public static function configure(Schema $schema): Schema
    {
        $tieneMovimientos = function (?string $operation, ?Banco $record): bool {
            return $record && $operation === 'edit' && MovimientoBanco::where('banco_id', $record->id)->exists();
        };

        return $schema
            ->components([
                TextInput::make('nombre_banco')
                    ->label('Nombre del Banco')
                    ->required()
                    ->maxLength(100)
                    ->disabled($tieneMovimientos),
                TextInput::make('numero_cuenta')
                    ->label('Número de Cuenta')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(50),
                Select::make('tipo_cuenta')
                    ->label('Tipo de Cuenta')
                    ->options(BancoTipoCuenta::class)
                    ->required()
                    ->disabled($tieneMovimientos),
                TextInput::make('saldo_inicial')
                    ->label('Saldo Inicial')
                    ->numeric()
                    ->prefix('$')
                    ->default(0)
                    ->disabled($tieneMovimientos),
                // moneda y codigo_swift ocultos — ver F6 en Tasklist
                Select::make('estado')
                    ->label('Estado')
                    ->options(BancoEstado::class)
                    ->default(BancoEstado::ACTIVO)
                    ->required()
                    ->disabled($tieneMovimientos),
            ]);
    }
}
