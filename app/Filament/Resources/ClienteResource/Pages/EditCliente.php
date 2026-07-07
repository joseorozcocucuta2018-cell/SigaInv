<?php

declare(strict_types=1);

namespace App\Filament\Resources\ClienteResource\Pages;

use App\Filament\Actions\GenerarPasswordPortalAction;
use App\Filament\Resources\ClienteResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCliente extends EditRecord
{
    protected static string $resource = ClienteResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            GenerarPasswordPortalAction::make(),
            DeleteAction::make(),
        ];
    }
}
