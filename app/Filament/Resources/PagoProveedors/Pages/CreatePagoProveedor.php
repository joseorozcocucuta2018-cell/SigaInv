<?php

declare(strict_types=1);

namespace App\Filament\Resources\PagoProveedors\Pages;

use App\Filament\Resources\PagoProveedors\PagoProveedorResource;
use App\Services\PagoProveedorService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePagoProveedor extends CreateRecord
{
    protected static string $resource = PagoProveedorResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return PagoProveedorService::crear($data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
