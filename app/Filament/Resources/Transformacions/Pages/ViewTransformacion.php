<?php

declare(strict_types=1);

namespace App\Filament\Resources\Transformacions\Pages;

use App\Filament\Resources\Transformacions\TransformacionResource;
use App\Services\TransformacionService;
use Exception;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewTransformacion extends ViewRecord
{
    protected static string $resource = TransformacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
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

            Action::make('volver')
                ->label('Volver')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(fn () => $this->getResource()::getUrl('index')),
        ];
    }
}
