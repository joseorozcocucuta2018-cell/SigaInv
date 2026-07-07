<?php

declare(strict_types=1);

namespace App\Filament\Resources\UnidadMedidas\Pages;

use App\Filament\Resources\UnidadMedidas\UnidadMedidaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUnidadMedida extends CreateRecord
{
    protected static string $resource = UnidadMedidaResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
