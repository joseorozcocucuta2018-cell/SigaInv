<?php

declare(strict_types=1);

namespace App\Filament\Resources\AjusteInventarios\Pages;

use App\Enums\AjusteEstado;
use App\Filament\Resources\AjusteInventarios\AjusteInventarioResource;
use App\Services\AjusteInventarioService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditAjusteInventario extends EditRecord
{
    protected static string $resource = AjusteInventarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('confirmar')
                ->label('Confirmar Ajuste')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Confirmar Ajuste de Inventario')
                ->modalDescription('¿Estás seguro? Se aplicarán las diferencias al stock de la bodega. Esta acción no se puede deshacer.')
                ->modalSubmitActionLabel('Sí, Confirmar')
                ->visible(fn () => $this->record->estado?->value === AjusteEstado::BORRADOR->value
                    && (Auth::user()?->can('ajuste_inventario.confirmar') ?? false))
                ->action(function () {
                    try {
                        $service = app(AjusteInventarioService::class);
                        $service->confirmar($this->record);

                        Notification::make()
                            ->title('Ajuste confirmado')
                            ->body('El ajuste de inventario ha sido confirmado y el stock actualizado.')
                            ->success()
                            ->send();

                        $this->redirect($this->getResource()::getUrl('index'));
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error al confirmar')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('anular')
                ->label('Anular Ajuste')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Anular Ajuste de Inventario')
                ->modalDescription('¿Estás seguro? Se revertirán los movimientos de inventario aplicados. Esta acción no se puede deshacer.')
                ->modalSubmitActionLabel('Sí, Anular')
                ->visible(fn () => $this->record->estado?->value === AjusteEstado::CONFIRMADO->value
                    && (Auth::user()?->can('ajuste_inventario.confirmar') ?? false))
                ->action(function () {
                    try {
                        $service = app(AjusteInventarioService::class);
                        $service->anular($this->record);

                        Notification::make()
                            ->title('Ajuste anulado')
                            ->body('El ajuste de inventario ha sido anulado y los movimientos revertidos.')
                            ->success()
                            ->send();

                        $this->redirect($this->getResource()::getUrl('index'));
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error al anular')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            DeleteAction::make()
                ->visible(fn () => $this->record->estado?->value === AjusteEstado::BORRADOR->value),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function beforeSave(): void
    {
        if ($this->record->estado !== AjusteEstado::BORRADOR) {
            throw new \InvalidArgumentException(
                "No se puede editar un ajuste en estado {$this->record->estado->label()}. Solo se pueden editar borradores."
            );
        }
    }
}
