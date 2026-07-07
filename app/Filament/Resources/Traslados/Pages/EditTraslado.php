<?php

declare(strict_types=1);

namespace App\Filament\Resources\Traslados\Pages;

use App\Filament\Resources\Traslados\TrasladoResource;
use Filament\Resources\Pages\EditRecord;

class EditTraslado extends EditRecord
{
    protected static string $resource = TrasladoResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
