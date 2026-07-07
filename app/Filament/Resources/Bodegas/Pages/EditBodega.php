<?php

declare(strict_types=1);

namespace App\Filament\Resources\Bodegas\Pages;

use App\Filament\Resources\Bodegas\BodegaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBodega extends EditRecord
{
    protected static string $resource = BodegaResource::class;

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
