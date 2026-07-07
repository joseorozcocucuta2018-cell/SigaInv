<?php

declare(strict_types=1);

namespace App\Filament\Resources\MovimientoCajas\Pages;

use App\Filament\Resources\MovimientoCajas\MovimientoCajaResource;
use Filament\Resources\Pages\EditRecord;

class EditMovimientoCaja extends EditRecord
{
    protected static string $resource = MovimientoCajaResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
