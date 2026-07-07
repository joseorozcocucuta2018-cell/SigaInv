<?php

declare(strict_types=1);

namespace App\Filament\Resources\PortalClientes\CotizacionPortals\Pages;

use App\Filament\Resources\PortalClientes\CotizacionPortals\CotizacionPortalResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCotizacionPortal extends CreateRecord
{
    protected static string $resource = CotizacionPortalResource::class;
}
