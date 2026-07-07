<?php

declare(strict_types=1);

namespace App\Filament\Resources\Impuestos\Pages;

use App\Filament\Resources\Impuestos\ImpuestoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateImpuesto extends CreateRecord
{
    protected static string $resource = ImpuestoResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
