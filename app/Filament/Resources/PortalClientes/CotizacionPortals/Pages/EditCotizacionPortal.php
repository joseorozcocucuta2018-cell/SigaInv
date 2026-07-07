<?php

declare(strict_types=1);

namespace App\Filament\Resources\PortalClientes\CotizacionPortals\Pages;

use App\Filament\Resources\PortalClientes\CotizacionPortals\CotizacionPortalResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCotizacionPortal extends EditRecord
{
    protected static string $resource = CotizacionPortalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
