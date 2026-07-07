<?php

declare(strict_types=1);

namespace App\Filament\Resources\Ventas\Actions;

use App\Enums\VentaEstado;
use App\Services\VentaService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class MarcarPagada extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'marcar-pagada')
            ->label('Marcar como Pagada')
            ->icon('heroicon-o-credit-card')
            ->color('info')
            ->requiresConfirmation()
            ->modalHeading('Marcar Venta como Pagada')
            ->modalDescription('¿Estás seguro de que deseas marcar esta venta como pagada? Si no está confirmada, se confirmará automáticamente.')
            ->modalSubmitActionLabel('Sí, Marcar como Pagada')
            ->action(function ($record) {
                try {
                    VentaService::marcarPagada($record);
                    Notification::make()
                        ->title('Venta marcada como pagada')
                        ->body('El estado de la venta ha sido actualizado.')
                        ->success()
                        ->send();
                    $record->refresh();
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Error al marcar como pagada')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            })
            ->visible(fn ($record) => in_array($record->estado?->value, [
                VentaEstado::BORRADOR->value,
                VentaEstado::CONFIRMADA->value,
            ]));
    }
}
