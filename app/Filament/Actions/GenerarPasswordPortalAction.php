<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use App\Models\Cliente;
use App\Services\PortalAccesoService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

/**
 * Header action para GENERAR una contraseña temporal y enviarla
 * por email al cliente.
 *
 * Solo es visible si el cliente puede tener acceso al portal
 * (portal_acceso = ACTIVO y email válido) — ver
 * App\Services\PortalAccesoService::puedeTenerAcceso().
 */
class GenerarPasswordPortalAction
{
    public static function make(): Action
    {
        return Action::make('generar-password-portal')
            ->label('Generar contraseña de acceso')
            ->icon('heroicon-o-key')
            ->color('primary')
            ->requiresConfirmation()
            ->modalHeading('Generar contraseña de acceso al portal')
            ->modalDescription('Se generará una contraseña aleatoria y se enviará al email del cliente. El cliente deberá cambiarla en su primer ingreso.')
            ->modalSubmitActionLabel('Sí, generar y enviar')
            ->visible(fn (Cliente $record): bool => app(PortalAccesoService::class)->puedeTenerAcceso($record))
            ->action(function (Cliente $record): void {
                try {
                    app(PortalAccesoService::class)->generarYEnviarPassword($record);

                    Notification::make()
                        ->title('Contraseña enviada')
                        ->body("Se envió una contraseña temporal a {$record->email}.")
                        ->success()
                        ->send();
                } catch (\Throwable $e) {
                    Notification::make()
                        ->title('No se pudo enviar el email')
                        ->body('La contraseña fue generada pero el email no se pudo enviar. Revisa los logs.')
                        ->danger()
                        ->send();
                }
            });
    }
}
