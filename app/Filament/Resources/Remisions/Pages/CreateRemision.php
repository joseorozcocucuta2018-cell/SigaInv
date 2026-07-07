<?php

declare(strict_types=1);

namespace App\Filament\Resources\Remisions\Pages;

use App\Filament\Resources\Numeracions\NumeracionResource;
use App\Filament\Resources\Remisions\RemisionResource;
use App\Services\NumeracionService;
use App\Traits\FiltersEmptyRepeaterRows;
use App\Traits\HasDocumentCreateActions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateRemision extends CreateRecord
{
    use FiltersEmptyRepeaterRows, HasDocumentCreateActions;

    protected static string $resource = RemisionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['usuario_id'] = auth()->id();
        $this->filterEmptyRepeaterRows($data);

        return $data;
    }

    public function mount(): void
    {
        parent::mount();

        if (! NumeracionService::tieneNumeracionActiva('remision')) {
            Notification::make()
                ->warning()
                ->title('No hay numeración configurada para remisiones')
                ->body('Debe crear una numeración tipo "Remisión" antes de registrar remisiones. Vaya a Configuración > Numeraciones.')
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
            $this->getSaveAndPrintAction('pdf.remision'),
            $this->getSaveAndEmailAction('remision', 'la remisión'),
            $this->getCancelFormAction(),
        ];
    }

    protected function afterCreate(): void
    {
        $this->handleDocumentCreated('pdf.remision', 'remision', [
            'cliente.ciudad', 'bodega', 'detalles.producto',
            'detalles.impuesto', 'usuario', 'cotizacion',
        ]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
