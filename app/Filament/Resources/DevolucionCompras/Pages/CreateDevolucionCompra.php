<?php

declare(strict_types=1);

namespace App\Filament\Resources\DevolucionCompras\Pages;

use App\Filament\Resources\DevolucionCompras\DevolucionCompraResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDevolucionCompra extends CreateRecord
{
    protected static string $resource = DevolucionCompraResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
