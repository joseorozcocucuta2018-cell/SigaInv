<?php

declare(strict_types=1);

namespace App\Filament\Resources\StockBodegas\Pages;

use App\Filament\Resources\StockBodegas\StockBodegaResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewStockBodega extends ViewRecord
{
    protected static string $resource = StockBodegaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
