<?php

declare(strict_types=1);

namespace App\Filament\Resources\FormaPagos\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class FormaPagoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nombre')
                    ->label('Nombre')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(60),
                Toggle::make('requiere_banco')
                    ->label('Requiere Banco')
                    ->helperText('Activar si este método necesita seleccionar una cuenta bancaria (ej: Transferencia, Cheque)')
                    ->default(false),
                Textarea::make('descripcion')
                    ->label('Descripción')
                    ->columnSpanFull(),
                Toggle::make('activo')
                    ->label('Activo')
                    ->default(true),
            ]);
    }
}
