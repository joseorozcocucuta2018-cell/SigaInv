<?php

declare(strict_types=1);

namespace App\Filament\Resources\Bancos\Pages;

use App\Filament\Resources\Bancos\BancoResource;
use App\Models\Banco;
use App\Services\BancosService;
use Filament\Resources\Pages\CreateRecord;

class CreateBanco extends CreateRecord
{
    protected static string $resource = BancoResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function handleRecordCreation(array $data): Banco
    {
        return BancosService::crearBanco($data);
    }
}
