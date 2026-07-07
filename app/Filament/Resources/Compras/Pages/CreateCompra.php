<?php

declare(strict_types=1);

namespace App\Filament\Resources\Compras\Pages;

use App\Filament\Resources\Compras\CompraResource;
use App\Traits\FiltersEmptyRepeaterRows;
use Filament\Resources\Pages\CreateRecord;

class CreateCompra extends CreateRecord
{
    use FiltersEmptyRepeaterRows;

    protected static string $resource = CompraResource::class;

    public function getHeading(): string
    {
        return 'Registrar Factura de Compra';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['usuario_id'] = auth()->id();
        $this->filterEmptyRepeaterRows($data);

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
