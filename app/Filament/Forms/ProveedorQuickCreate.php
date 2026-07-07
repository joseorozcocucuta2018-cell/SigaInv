<?php

declare(strict_types=1);

namespace App\Filament\Forms;

use App\Enums\ProveedorEstado;
use App\Models\Proveedor;
use Closure;
use Filament\Forms\Components\Component;
use Filament\Notifications\Notification;

class ProveedorQuickCreate
{
    use PersonaQuickCreateFields;

    /**
     * @return array<int, Component>
     */
    public static function form(): array
    {
        return self::personaFields(Proveedor::class);
    }

    public static function using(): Closure
    {
        return function (array $data): int {
            $data['nombre'] = mb_convert_case(trim($data['nombre']), MB_CASE_TITLE, 'UTF-8');
            $proveedor = Proveedor::create([
                ...$data,
                'departamento_id' => 54,   // Norte de Santander
                'ciudad_id' => 889,        // Cúcuta
                'estado' => ProveedorEstado::ACTIVO,
            ]);

            Notification::make()
                ->title('Proveedor creado')
                ->body('Complete los datos adicionales (contacto, condiciones comerciales) desde el módulo Proveedores.')
                ->info()
                ->send();

            return $proveedor->id;
        };
    }
}
