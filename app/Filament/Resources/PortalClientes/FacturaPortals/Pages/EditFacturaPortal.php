<?php

declare(strict_types=1);

namespace App\Filament\Resources\PortalClientes\FacturaPortals\Pages;

use App\Filament\Resources\PortalClientes\FacturaPortals\FacturaPortalResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFacturaPortal extends EditRecord
{
    protected static string $resource = FacturaPortalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
