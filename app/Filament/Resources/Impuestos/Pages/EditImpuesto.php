<?php

declare(strict_types=1);

namespace App\Filament\Resources\Impuestos\Pages;

use App\Filament\Resources\Impuestos\ImpuestoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditImpuesto extends EditRecord
{
    protected static string $resource = ImpuestoResource::class;

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
