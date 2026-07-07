<?php

declare(strict_types=1);

namespace App\Filament\Resources\PortalClientes\RemisionPortals\Pages;

use App\Filament\Resources\PortalClientes\RemisionPortals\RemisionPortalResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRemisionPortal extends EditRecord
{
    protected static string $resource = RemisionPortalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
