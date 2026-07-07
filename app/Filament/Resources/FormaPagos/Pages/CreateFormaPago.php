<?php

declare(strict_types=1);

namespace App\Filament\Resources\FormaPagos\Pages;

use App\Filament\Resources\FormaPagos\FormaPagoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFormaPago extends CreateRecord
{
    protected static string $resource = FormaPagoResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
