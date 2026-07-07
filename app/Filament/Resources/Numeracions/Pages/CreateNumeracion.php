<?php

declare(strict_types=1);

namespace App\Filament\Resources\Numeracions\Pages;

use App\Filament\Resources\Numeracions\NumeracionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateNumeracion extends CreateRecord
{
    protected static string $resource = NumeracionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
