<?php

declare(strict_types=1);

namespace App\Filament\Forms;

use App\Enums\ClienteEstado;
use App\Models\Cliente;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\HtmlString;

/**
 * Helper para Selects de cliente con validación visual de email.
 *
 * Crea un Select de cliente (de Cliente::class) con:
 *  - helperText dinámico que muestra si el cliente tiene o no email
 *  - asterisco indicando que es requerido
 *  - searchable + preload
 *
 * Uso:
 *   ClienteSelect::make('cliente_id')
 *       ->required(),
 */
final class ClienteSelect
{
    public static function make(string $name = 'cliente_id'): Select
    {
        return Select::make($name)
            ->label('Cliente')
            ->options(Cliente::where('estado', ClienteEstado::ACTIVO)->pluck('nombre', 'id'))
            ->searchable()
            ->preload()
            ->live()
            ->helperText(function (Get $get, ?string $state): HtmlString|string {
                if (empty($state)) {
                    return '';
                }

                $cliente = Cliente::find($state);

                if (! $cliente) {
                    return '';
                }

                if (empty($cliente->email)) {
                    return new HtmlString(
                        '<span style="color:#dc2626;">⚠️ Cliente sin email — no se podrán enviar comprobantes automáticamente.</span>'
                    );
                }

                return "📧 {$cliente->email}";
            });
    }
}
