<?php

declare(strict_types=1);

namespace App\Filament\Resources\AuditoriaDocumentos\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AuditoriaDocumentoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('documento_tipo')
                    ->required(),
                TextInput::make('documento_id')
                    ->required()
                    ->numeric(),
                Select::make('usuario_id')
                    ->relationship('usuario', 'name'),
                TextInput::make('campo_modificado'),
                Textarea::make('valor_anterior')
                    ->columnSpanFull(),
                Textarea::make('valor_nuevo')
                    ->columnSpanFull(),
                TextInput::make('estado_documento')
                    ->required(),
                TextInput::make('accion')
                    ->required(),
                Textarea::make('observacion')
                    ->columnSpanFull(),
                TextInput::make('ip_address'),
                Textarea::make('user_agent')
                    ->columnSpanFull(),
            ]);
    }
}
