<?php

declare(strict_types=1);

namespace App\Filament\Resources\FormaPagos\Pages;

use App\Filament\Resources\FormaPagos\FormaPagoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFormaPago extends EditRecord
{
    protected static string $resource = FormaPagoResource::class;

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
