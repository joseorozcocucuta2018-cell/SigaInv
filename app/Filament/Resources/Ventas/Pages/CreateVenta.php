<?php

declare(strict_types=1);

namespace App\Filament\Resources\Ventas\Pages;

use App\Filament\Resources\Ventas\VentaResource;
use App\Traits\FiltersEmptyRepeaterRows;
use App\Traits\HasDocumentCreateActions;
use Filament\Resources\Pages\CreateRecord;

class CreateVenta extends CreateRecord
{
    use FiltersEmptyRepeaterRows, HasDocumentCreateActions;

    protected static string $resource = VentaResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['usuario_id'] = auth()->id();
        $this->filterEmptyRepeaterRows($data);

        return $data;
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            $this->getSaveAndPrintAction('pdf.venta'),
            $this->getSaveAndEmailAction('venta', 'la venta'),
            $this->getCancelFormAction(),
        ];
    }

    protected function afterCreate(): void
    {
        $this->handleDocumentCreated('pdf.venta', 'venta', [
            'cliente.ciudad', 'bodega', 'detalles.producto', 'detalles.impuesto',
            'usuario', 'cotizacion', 'remision',
        ]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
