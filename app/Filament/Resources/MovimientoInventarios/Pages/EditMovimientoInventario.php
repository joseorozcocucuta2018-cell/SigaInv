<?php

declare(strict_types=1);

namespace App\Filament\Resources\MovimientoInventarios\Pages;

use App\Filament\Resources\MovimientoInventarios\MovimientoInventarioResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditMovimientoInventario extends EditRecord
{
    protected static string $resource = MovimientoInventarioResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
