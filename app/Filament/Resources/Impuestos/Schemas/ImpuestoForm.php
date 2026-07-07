<?php

declare(strict_types=1);

namespace App\Filament\Resources\Impuestos\Schemas;

use App\Enums\ImpuestoTipo;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ImpuestoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nombre')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(100),
                Select::make('tipo')
                    ->label('Tipo')
                    ->options(ImpuestoTipo::class)
                    ->default('IVA')
                    ->required(),
                TextInput::make('porcentaje')
                    ->label('Porcentaje (%)')
                    ->numeric()
                    ->suffix('%')
                    ->required(),
                Textarea::make('descripcion')
                    ->label('Descripción')
                    ->columnSpanFull(),
                Toggle::make('activo')
                    ->label('Activo')
                    ->default(true),
            ]);
    }
}
