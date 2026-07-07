<?php

declare(strict_types=1);

namespace App\Filament\Resources\Cotizacions\Pages;

use App\Enums\CotizacionEstado;
use App\Filament\Actions\PrintAction;
use App\Filament\Resources\Cotizacions\CotizacionResource;
use App\Services\CotizacionService;
use App\Traits\SendsDocumentEmail;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditCotizacion extends EditRecord
{
    use SendsDocumentEmail;

    protected static string $resource = CotizacionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            PrintAction::make('pdf.cotizacion'),

            $this->makeEnviarCorreoAction(
                'cotizacion',
                ['cliente.ciudad', 'bodega', 'detalles.producto', 'detalles.impuesto', 'usuario'],
                afterSend: function (array $data) {
                    if ($this->record->estado?->value === CotizacionEstado::PENDIENTE->value) {
                        CotizacionService::cambiarEstado($this->record, CotizacionEstado::ENVIADA);
                    }
                },
            ),

            DeleteAction::make()
                ->visible(fn () => $this->record->estado?->value === CotizacionEstado::PENDIENTE->value),
        ];
    }

    protected function afterSave(): void
    {
        if ($this->record->estado?->value === CotizacionEstado::ENVIADA->value) {
            Notification::make()
                ->info()
                ->title('Cotización actualizada')
                ->body('La cotización fue modificada. Considere reimprimir o reenviar al cliente para reflejar los cambios.')
                ->persistent()
                ->send();
        }
    }
}
