<?php

declare(strict_types=1);

namespace App\Filament\Resources\Cotizacions\Pages;

use App\Enums\CotizacionEstado;
use App\Filament\Resources\Cotizacions\CotizacionResource;
use App\Filament\Resources\Numeracions\NumeracionResource;
use App\Services\CotizacionService;
use App\Services\NumeracionService;
use App\Traits\FiltersEmptyRepeaterRows;
use App\Traits\HasDocumentCreateActions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateCotizacion extends CreateRecord
{
    use FiltersEmptyRepeaterRows, HasDocumentCreateActions;

    protected static string $resource = CotizacionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['usuario_id'] = auth()->id();
        $this->filterEmptyRepeaterRows($data);

        return $data;
    }

    public function mount(): void
    {
        parent::mount();

        if (! NumeracionService::tieneNumeracionActiva('cotizacion')) {
            Notification::make()
                ->warning()
                ->title('No hay numeración configurada para cotizaciones')
                ->body('Debe crear una numeración tipo "Cotización" antes de registrar cotizaciones. Vaya a Configuración > Numeraciones.')
                ->actions([
                    Action::make('crear_numeracion')
                        ->label('Crear Numeración')
                        ->url(NumeracionResource::getUrl('create'), shouldOpenInNewTab: true),
                ])
                ->persistent()
                ->send();
        }
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            $this->getSaveAndPrintAction('pdf.cotizacion'),
            $this->getSaveAndEmailAction('cotizacion', 'la cotización'),
            $this->getCancelFormAction(),
        ];
    }

    protected function afterCreate(): void
    {
        $this->handleDocumentCreated('pdf.cotizacion', 'cotizacion', [
            'cliente.ciudad', 'bodega', 'detalles.producto',
            'detalles.impuesto', 'usuario',
        ]);
    }

    protected function onEmailSent(): void
    {
        if ($this->record->estado?->value === CotizacionEstado::PENDIENTE->value) {
            CotizacionService::cambiarEstado($this->record, CotizacionEstado::ENVIADA);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
