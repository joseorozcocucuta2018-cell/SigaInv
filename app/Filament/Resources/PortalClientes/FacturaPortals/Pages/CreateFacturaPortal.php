<?php

declare(strict_types=1);

namespace App\Filament\Resources\PortalClientes\FacturaPortals\Pages;

use App\Filament\Resources\PortalClientes\FacturaPortals\FacturaPortalResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFacturaPortal extends CreateRecord
{
    protected static string $resource = FacturaPortalResource::class;
}
