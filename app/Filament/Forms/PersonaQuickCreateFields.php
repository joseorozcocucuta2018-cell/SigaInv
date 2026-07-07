<?php

declare(strict_types=1);

namespace App\Filament\Forms;

use App\Enums\DocumentoTipoEnum;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

/**
 * Trait con los campos comunes del QuickCreate de Personas (Clientes / Proveedores).
 * Reduce duplicación entre ClienteQuickCreate y ProveedorQuickCreate.
 *
 * Uso:
 *   class ClienteQuickCreate
 *   {
 *       use PersonaQuickCreateFields;
 *
 *       public static function form(): array
 *       {
 *           return self::personaFields();  // sin unique rules
 *       }
 *   }
 *
 *   class ProveedorQuickCreate
 *   {
 *       use PersonaQuickCreateFields;
 *
 *       public static function form(): array
 *       {
 *           return self::personaFields(Proveedor::class);  // con unique rules
 *       }
 *   }
 */
trait PersonaQuickCreateFields
{
    /**
     * @return array<int, Component>
     */
    public static function personaFields(?string $modelClass = null): array
    {
        $documento = TextInput::make('documento')
            ->label('NIT / Documento')
            ->required();

        $email = TextInput::make('email')
            ->email()
            ->required();

        if ($modelClass !== null) {
            $documento->unique(table: $modelClass, column: 'documento');
            $email->unique(table: $modelClass, column: 'email');
        }

        return [
            TextInput::make('nombre')
                ->label('Nombre / Razón Social')
                ->required()
                ->maxLength(100),
            Select::make('tipo_documento')
                ->options(DocumentoTipoEnum::class)
                ->required(),
            $documento,
            TextInput::make('telefono')
                ->required(),
            $email,
            TextInput::make('direccion1')
                ->label('Dirección')
                ->required(),
        ];
    }
}
