<?php

declare(strict_types=1);

namespace App\Filament\Resources\Transformacions\Pages;

use App\Enums\TransformacionEstado;
use App\Filament\Resources\Transformacions\TransformacionResource;
use App\Services\TransformacionService;
use Exception;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditTransformacion extends EditRecord
{
    protected static string $resource = TransformacionResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        if ($this->record->detalles()->count() === 0 && $this->record->formula_transformacion_id) {
            app(TransformacionService::class)->cargarDetallesDesdeFormula($this->record);
            $this->fillForm();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('confirmar')
                ->label('Confirmar')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => $this->record->estado?->value === TransformacionEstado::BORRADOR->value)
                ->requiresConfirmation()
                ->modalHeading('Confirmar transformación')
                ->modalDescription('Se descontarán los insumos y se sumará el producto final al stock. Esta acción no se puede deshacer (salvo Combo/Promo).')
                ->modalSubmitActionLabel('Sí, confirmar')
                ->action(function () {
                    try {
                        app(TransformacionService::class)->confirmar($this->record);

                        Notification::make()
                            ->title('Transformación confirmada')
                            ->body('Los movimientos de stock han sido aplicados correctamente.')
                            ->success()
                            ->send();

                        $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
                    } catch (Exception $e) {
                        Notification::make()
                            ->title('Error al confirmar')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('revertir')
                ->label('Revertir')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('warning')
                ->visible(fn () => $this->record->puedeRevertirse())
                ->requiresConfirmation()
                ->modalHeading('Revertir transformación')
                ->modalDescription('Se devolverán los insumos al stock y se descontará el producto final. Solo es posible si hay stock suficiente del producto generado.')
                ->modalSubmitActionLabel('Sí, revertir')
                ->action(function () {
                    try {
                        app(TransformacionService::class)->revertir($this->record);

                        Notification::make()
                            ->title('Transformación revertida')
                            ->body('Los movimientos de stock han sido revertidos.')
                            ->warning()
                            ->send();

                        $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
                    } catch (Exception $e) {
                        Notification::make()
                            ->title('Error al revertir')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Actions\DeleteAction::make()
                ->visible(fn () => $this->record->estado?->value === TransformacionEstado::BORRADOR->value),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! isset($data['usuario_id'])) {
            $data['usuario_id'] = Auth::id();
        }

        return $data;
    }

    protected function afterSave(): void
    {
        if ($this->record->formula_transformacion_id) {
            try {
                app(TransformacionService::class)->reapplyFormulaIfNeeded($this->record);

                Notification::make()
                    ->title('Componentes actualizados')
                    ->body('Insumos cargados desde la fórmula.')
                    ->success()
                    ->send();
            } catch (Exception $e) {
                Notification::make()
                    ->title('Error al cargar fórmula')
                    ->body($e->getMessage())
                    ->danger()
                    ->send();
            }
        }
    }
}
