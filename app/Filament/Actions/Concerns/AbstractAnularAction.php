<?php

declare(strict_types=1);

namespace App\Filament\Actions\Concerns;

use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Throwable;

/**
 * Base para botones "Anular" en Filament Resources.
 *
 * Estandariza: confirmation modal con campo razón opcional/requerido →
 * service call → notification → refresh.
 */
abstract class AbstractAnularAction extends Action
{
    protected static string $documentName = '';

    protected static ?string $serviceClass = null;

    protected static string $serviceMethod = '';

    /**
     * @var array<int, string>
     */
    protected static array $visibleStates = [];

    /**
     * Si la razón es obligatoria.
     */
    protected static bool $razonRequired = false;

    /**
     * Verbo principal (Anular / Cancelar).
     */
    protected static string $verbo = 'Anular';

    /**
     * Body de la notificación de éxito.
     */
    protected static ?string $customNotificationBody = null;

    public static function make(?string $name = null): static
    {
        $documentName = static::$documentName;
        $verbo = static::$verbo;
        $razonRequired = static::$razonRequired;
        $verboLower = mb_strtolower($verbo);

        return parent::make($name ?? 'anular')
            ->label("{$verbo} {$documentName}")
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading("{$verbo} {$documentName}")
            ->modalDescription('¿Estás seguro? Esta acción puede revertir movimientos de inventario y no se puede deshacer.')
            ->modalSubmitActionLabel("Sí, {$verbo}")
            ->form([
                Textarea::make('razon')
                    ->label("Razón de {$verboLower}".($razonRequired ? '' : ' (opcional)'))
                    ->placeholder('Especifica el motivo...')
                    ->required($razonRequired)
                    ->rows(3),
            ])
            ->action(function ($record, array $data) use ($documentName, $verboLower) {
                try {
                    app(static::$serviceClass)->{static::$serviceMethod}($record, $data['razon'] ?? null);
                    Notification::make()
                        ->title("{$documentName} {$verboLower}/a")
                        ->body(static::$customNotificationBody ?? 'El documento ha sido anulado correctamente.')
                        ->success()
                        ->send();
                    $record->refresh();
                } catch (Throwable $e) {
                    Notification::make()
                        ->title("Error al {$verboLower} {$documentName}")
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
