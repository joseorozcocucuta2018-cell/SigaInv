<?php

declare(strict_types=1);

namespace App\Filament\Resources\StockBodegas\Pages;

use App\Filament\Resources\StockBodegas\StockBodegaResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditStockBodega extends EditRecord
{
    protected static string $resource = StockBodegaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
