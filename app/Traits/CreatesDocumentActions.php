<?php

declare(strict_types=1);

namespace App\Traits;

use BackedEnum;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;

/**
 * Trait para crear actions genéricos de Confirmar/Anular documentos.
 *
 * Uso en Resource:
 *   use CreatesDocumentActions;
 *
 *   // En getHeaderActions():
 *   $this->makeConfirmarAction(
 *       entity: 'venta',
 *       service: fn($r) => VentaService::confirmar($r),
 *       estadoEnum: VentaEstado::BORRADOR,
 *   )
 */
trait CreatesDocumentActions
{
    protected function makeConfirmarAction(
        string $entity,
        Closure $service,
        BackedEnum $estadoBorrador,
        ?string $modalDescription = null,
        ?string $successTitle = null,
        ?string $successBody = null,
    ): Action {
        $entityLabel = ucfirst($entity);
        $modalDescription ??= "¿Estás seguro de que deseas confirmar {$this->article($entity)}? Esta acción no se puede deshacer.";
        $successTitle ??= "{$entityLabel} confirmad".($this->isFeminine($entity) ? 'a' : 'o');
        $successBody ??= "{$entityLabel} se ha confirmado correctamente.";

        return Action::make('confirmar')
            ->label("Confirmar {$entityLabel}")
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading("Confirmar {$entityLabel}")
            ->modalDescription($modalDescription)
            ->modalSubmitActionLabel('Sí, Confirmar')
            ->action(function ($record) use ($service, $successTitle, $successBody) {
                try {
                    $service($record);
                    Notification::make()
                        ->title($successTitle)
                        ->body($successBody)
                        ->success()
                        ->send();
                    $record->refresh();
                } catch (\Exception $e) {
                    Notification::make()
                        ->title("Error al confirmar {$entity}")
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            })
            ->visible(fn ($record) => $record->estado?->value === $estadoBorrador->value);
    }

    protected function makeReversarAction(
        string $entity,
        Closure $service,
        BackedEnum $estadoReversable,
        bool $requiresReason = false,
        ?string $modalDescription = null,
        ?Closure $visible = null,
    ): Action {
        $entityLabel = ucfirst($entity);
        $modalDescription ??= "¿Estás seguro de que deseas reversar {$this->article($entity)}?";

        $action = Action::make('reversar')
            ->label("Reversar {$entityLabel}")
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading("Reversar {$entityLabel}")
            ->modalDescription($modalDescription)
            ->modalSubmitActionLabel('Sí, Reversar')
            ->action(function ($record) use ($service, $entity) {
                try {
                    $service($record);
                    Notification::make()
                        ->title("{$entityLabel} reversad".($this->isFeminine($entity) ? 'a' : 'o'))
                        ->body("{$entityLabel} se ha reversado correctamente.")
                        ->success()
                        ->send();
                    $record->refresh();
                } catch (\Exception $e) {
                    Notification::make()
                        ->title("Error al reversar {$entity}")
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            })
            ->visible(function ($record) use ($estadoReversable, $visible) {
                if ($visible !== null) {
                    return $visible($record);
                }

                return $record->estado?->value !== $estadoReversable->value;
            });

        if ($requiresReason) {
            $action->schema([
                Textarea::make('razon')
                    ->label('Motivo de la reversión')
                    ->required()
                    ->rows(3),
            ]);
        }

        return $action;
    }

    protected function article(string $entity): string
    {
        return $this->isFeminine($entity) ? 'la' : 'el';
    }

    protected function isFeminine(string $entity): bool
    {
        return in_array($entity, ['venta', 'remision', 'cotizacion', 'devolucion', 'compra'], true);
    }
}
