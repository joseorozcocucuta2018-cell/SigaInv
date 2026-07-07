<?php

declare(strict_types=1);

namespace App\Filament\Resources\Numeracions\Pages;

use App\Filament\Resources\Numeracions\NumeracionResource;
use App\Services\NumeracionService;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditNumeracion extends EditRecord
{
    protected static string $resource = NumeracionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Verificar si ya está en uso
        $enUso = NumeracionService::estaEnUso($this->getRecord());

        // Agregar indicador visual
        $data['en_uso'] = $enUso;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Verificar si ya está en uso - impedir edición de consecutivos si ya se usó
        if (NumeracionService::estaEnUso($this->getRecord())) {
            // Solo permitir cambiar campos no críticos
            unset($data['consecutivo_desde'], $data['consecutivo_hasta'], $data['consecutivo_actual']);

            Notification::make()
                ->warning()
                ->title('Numeración en uso')
                ->body('No se pueden modificar los rangos de una numeración que ya tiene documentos emitidos.')
                ->send();
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->hidden(function () {
                    return NumeracionService::estaEnUso($this->getRecord());
                })
                ->disabled(function () {
                    return NumeracionService::estaEnUso($this->getRecord());
                }),
        ];
    }

    protected function canDelete(): bool
    {
        return ! NumeracionService::estaEnUso($this->getRecord());
    }
}
