<?php

declare(strict_types=1);

namespace App\Filament\Resources\Traslados\Actions;

use App\Enums\TrasladoEstado;
use App\Services\TrasladoService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class RevertirTraslado extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'revertir')
            ->label('Revertir')
            ->icon('heroicon-o-arrow-uturn-left')
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading('Revertir Traslado')
            ->modalDescription('¿Estás seguro de que deseas revertir este traslado? El stock será devuelto a la bodega origen.')
            ->modalSubmitActionLabel('Sí, Revertir')
            ->action(function ($record) {
                try {
                    app(TrasladoService::class)->revertir($record);
                    Notification::make()
                        ->title('Traslado revertido')
                        ->body('El traslado ha sido revertido y el stock fue devuelto a la bodega origen.')
                        ->success()
                        ->send();
                    $record->refresh();
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Error al revertir traslado')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            })
            ->visible(fn ($record) => $record->estado?->value === TrasladoEstado::CONFIRMADA->value);
    }
}
