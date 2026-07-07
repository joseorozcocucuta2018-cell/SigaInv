<?php

declare(strict_types=1);

namespace App\Filament\Forms;

use App\Enums\ClienteEstado;
use App\Models\Cliente;
use Closure;
use Filament\Forms\Components\Component;
use Filament\Notifications\Notification;

class ClienteQuickCreate
{
    use PersonaQuickCreateFields;

    /**
     * @return array<int, Component>
     */
    public static function form(): array
    {
        return self::personaFields();
    }

    public static function using(): Closure
    {
        return function (array $data): int {
            $data['nombre'] = mb_convert_case(trim($data['nombre']), MB_CASE_TITLE, 'UTF-8');
            $cliente = Cliente::create([
                ...$data,
                'departamento_id' => 54,   // Norte de Santander
                'ciudad_id' => 889,        // Cúcuta
                'estado' => ClienteEstado::ACTIVO,
            ]);

            Notification::make()
                ->title('Cliente creado')
                ->body('Complete los datos adicionales (contacto, condiciones comerciales) desde el módulo Clientes.')
                ->info()
                ->send();

            return $cliente->id;
        };
    }
}
