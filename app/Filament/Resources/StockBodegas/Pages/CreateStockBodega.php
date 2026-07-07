<?php

declare(strict_types=1);

namespace App\Filament\Resources\StockBodegas\Pages;

use App\Filament\Resources\StockBodegas\StockBodegaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStockBodega extends CreateRecord
{
    protected static string $resource = StockBodegaResource::class;
}
