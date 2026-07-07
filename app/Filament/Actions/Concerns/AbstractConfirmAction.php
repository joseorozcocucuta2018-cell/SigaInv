<?php

declare(strict_types=1);

namespace App\Filament\Actions\Concerns;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Throwable;

/**
 * Base para botones "Confirmar" en Filament Resources.
 *
 * Estandariza el flujo: confirmation modal → service call → notification → refresh.
 * Las subclases definen: nombre del documento, estados visibles, método del service,
 * textos (label, modal, notificación).
 */
abstract class AbstractConfirmAction extends Action
{
    /**
     * Nombre legible del documento (ej: 'Venta', 'Remisión', 'Traslado').
     */
    protected static string $documentName = '';

    /**
     * Clase del service a invocar.
     */
    protected static ?string $serviceClass = null;

    /**
     * Método del service a invocar.
     */
    protected static string $serviceMethod = '';

    /**
     * Estados en los que la acción es visible.
     *
     * @var array<int, string>
     */
    protected static array $visibleStates = [];

    /**
     * Texto del body en la notificación de éxito. Default razonable.
     */
    protected static ?string $customNotificationBody = null;

    public static function make(?string $name = null): static
    {
        $documentName = static::$documentName;

        return parent::make($name ?? 'confirmar')
            ->label("Confirmar {$documentName}")
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading("Confirmar {$documentName}")
            ->modalDescription('¿Estás seguro de que deseas confirmar este documento? Se generarán los movimientos de inventario y no podrá ser editado.')
            ->modalSubmitActionLabel('Sí, Confirmar')
            ->action(function ($record) use ($documentName) {
                try {
                    app(static::$serviceClass)->{static::$serviceMethod}($record);
                    Notification::make()
                        ->title("{$documentName} confirmado/a")
                        ->body(static::$customNotificationBody ?? 'El documento se ha confirmado y los movimientos de inventario han sido generados.')
                        ->success()
                        ->send();
                    $record->refresh();
                } catch (Throwable $e) {
                    Notification::make()
                        ->title("Error al confirmar {$documentName}")
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            })
            ->visible(function ($record) {
                $estado = $record->estado?->value;

                return $estado !== null && in_array($estado, static::$visibleStates, true);
            });
    }
}
