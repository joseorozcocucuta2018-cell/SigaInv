<?php

declare(strict_types=1);

namespace App\Filament\Resources\MovimientoInventarios\Pages;

use App\Filament\Resources\MovimientoInventarios\MovimientoInventarioResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMovimientoInventario extends CreateRecord
{
    protected static string $resource = MovimientoInventarioResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
