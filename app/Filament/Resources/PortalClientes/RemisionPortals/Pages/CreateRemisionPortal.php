<?php

declare(strict_types=1);

namespace App\Filament\Resources\PortalClientes\RemisionPortals\Pages;

use App\Filament\Resources\PortalClientes\RemisionPortals\RemisionPortalResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRemisionPortal extends CreateRecord
{
    protected static string $resource = RemisionPortalResource::class;
}
