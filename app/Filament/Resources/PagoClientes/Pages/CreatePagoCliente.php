<?php

declare(strict_types=1);

namespace App\Filament\Resources\PagoClientes\Pages;

use App\Filament\Resources\PagoClientes\PagoClienteResource;
use App\Services\PagoClienteService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePagoCliente extends CreateRecord
{
    protected static string $resource = PagoClienteResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return PagoClienteService::crear($data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
