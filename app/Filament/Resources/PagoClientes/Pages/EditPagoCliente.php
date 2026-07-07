<?php

declare(strict_types=1);

namespace App\Filament\Resources\PagoClientes\Pages;

use App\Filament\Resources\PagoClientes\PagoClienteResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPagoCliente extends EditRecord
{
    protected static string $resource = PagoClienteResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
