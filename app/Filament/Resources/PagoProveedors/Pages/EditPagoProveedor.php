<?php

declare(strict_types=1);

namespace App\Filament\Resources\PagoProveedors\Pages;

use App\Filament\Resources\PagoProveedors\PagoProveedorResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPagoProveedor extends EditRecord
{
    protected static string $resource = PagoProveedorResource::class;

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
