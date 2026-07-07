<?php

declare(strict_types=1);

namespace App\Filament\Resources\Marcas\Pages;

use App\Filament\Resources\Marcas\MarcaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMarca extends CreateRecord
{
    protected static string $resource = MarcaResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
