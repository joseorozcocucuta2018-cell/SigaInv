<?php

declare(strict_types=1);

namespace App\Filament\Resources\Categorias\Schemas;

use App\Models\Categoria;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CategoriaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nombre')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(100),
                Select::make('categoria_id')
                    ->label('Categoría Padre')
                    ->options(Categoria::where('activo', true)->pluck('nombre', 'id'))
                    ->searchable()
                    ->placeholder('Ninguna (raíz)'),
                Textarea::make('descripcion')
                    ->label('Descripción')
                    ->columnSpanFull(),
                Toggle::make('activo')
                    ->label('Activo')
                    ->default(true),
            ]);
    }
}
