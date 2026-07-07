<?php

declare(strict_types=1);

namespace App\Filament\Resources\Devoluciones\Pages;

use App\Filament\Resources\Devoluciones\DevolucionResource;
use App\Traits\FiltersEmptyRepeaterRows;
use Filament\Resources\Pages\CreateRecord;

class CreateDevolucion extends CreateRecord
{
    use FiltersEmptyRepeaterRows;

    protected static string $resource = DevolucionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->filterEmptyRepeaterRows($data);

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
